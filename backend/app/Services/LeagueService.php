<?php

namespace App\Services;

use App\Models\Team;
use App\Models\Fixture;

class LeagueService
{

    protected $matchEngine;
    public function __construct(MatchEngineService $me)
    {
        $this->matchEngine = $me;
    }
    public function getTable()
    {
        $teams = Team::all()->map(function ($team) {
            $matches = Fixture::where('is_played', true)
                ->where(function ($q) use ($team) {
                    $q->where('home_team_id', $team->id)->orWhere('away_team_id', $team->id);
                })
                ->get();

            $p = $w = $d = $l = $gs = $gc = 0;
            foreach ($matches as $m) {
                $isHome = $m->home_team_id == $team->id;
                $myScore = $isHome ? $m->home_score : $m->away_score;
                $oppScore = $isHome ? $m->away_score : $m->home_score;
                $gs += $myScore;
                $gc += $oppScore;
                if ($myScore > $oppScore) {
                    $w++;
                } elseif ($myScore == $oppScore) {
                    $d++;
                } else {
                    $l++;
                }
            }
            $recentForm = [];
            $last5Matches = $matches->sortBy('week')->take(-5); // Son 5 maç

            foreach ($last5Matches as $m) {
                $isHome = $m->home_team_id == $team->id;
                $myScore = $isHome ? $m->home_score : $m->away_score;
                $oppScore = $isHome ? $m->away_score : $m->home_score;

                if ($myScore > $oppScore) {
                    $recentForm[] = 'W'; // Galibiyet (Win)
                } elseif ($myScore < $oppScore) {
                    $recentForm[] = 'L'; // Mağlubiyet (Lose)
                } else {
                    $recentForm[] = 'D'; // Beraberlik (Draw)
                }
            }
            return [
                'id' => $team->id,
                'name' => $team->name,
                'p' => $matches->count(),
                'w' => $w,
                'd' => $d,
                'l' => $l,
                'gf' => $gs,
                'ga' => $gc,
                'gd' => $gs - $gc,
                'pts' => ($w * 3) + $d,
                'form' => $team->form,
                'recent_form' => $recentForm
            ];
        })->toArray();

        // 1. Puan (Azalan), 2. Averaj (Azalan), 3. Atılan Gol (Azalan)
        usort($teams, function ($a, $b) {
            if ($a['pts'] !== $b['pts']) {
                return $b['pts'] <=> $a['pts'];
            }
            if ($a['gd'] !== $b['gd']) {
                return $b['gd'] <=> $a['gd'];
            }
            return $b['gf'] <=> $a['gf'];
        });

        return collect($teams);
    }

    public function getPredictions($table)
    {
        $unplayedFixtures = Fixture::where('is_played', false)->get();

        if ($unplayedFixtures->isEmpty()) {
            $champion = $table->first();
            return $table->map(function ($t) use ($champion) {
                return ['team_name' => $t['name'], 'percentage' => $t['id'] == $champion['id'] ? 100 : 0];
            });
        }

        // 1. Matematiksel olarak şampiyonluk şansı kalmayanları ele (Elimination)
        $maxPointsAvailable = ($unplayedFixtures->count() / 2) * 3; // Kalan hafta sayısı * 3 puan
        $currentLeaderPoints = $table->max('pts');

        $table = $table->map(function ($team) use ($maxPointsAvailable, $currentLeaderPoints) {
            $team['can_win'] = ($team['pts'] + $maxPointsAvailable) >= $currentLeaderPoints;
            return $team;
        });

        // 2. Simülasyonu çalıştır (Artık 5000 iterasyon yapıyoruz ki çok düşük ihtimalleri de yakalasın)
        $predictions = $this->runMonteCarloSimulation($table, $unplayedFixtures, 5000);

        // 3. Matematiksel olarak şansı olan ama simülasyonda 0 çeken takımlara en az %1 şans ver (Görsel Düzeltme)
        return array_map(function ($p) use ($table) {
            $teamInfo = $table->firstWhere('name', $p['team_name']);
            if ($teamInfo['can_win'] && $p['percentage'] == 0) {
                $p['percentage'] = 1; // "Matematiksel olarak şansı var ama çok düşük (< %0.1)" anlamına gelir.
            }
            return $p;
        }, $predictions);
    }


    /**
     * Runs a Monte Carlo simulation for championship predictions.
     */
    private function runMonteCarloSimulation($currentTable, $unplayedFixtures, $simulations = 1000)
    {

        // 1. Modeller yerine takımların anlık istatistiklerini KOPYALAYIP bir dizi (array) yapıyoruz.
        // Böylece veritabanıyla olan bağ tamamen kopuyor.
        $basePoints = [];
        $teamsStats = [];

        foreach ($currentTable as $t) {
            $basePoints[$t['id']] = $t['pts'];

            $teamModel = Team::find($t['id']);
            $teamsStats[$t['id']] = [
                'attack_strength' => $teamModel->attack_strength,
                'defense_strength' => $teamModel->defense_strength,
                'form' => $teamModel->form
            ];
        }

        $winCounts = array_fill_keys(array_keys($basePoints), 0);

        for ($i = 0; $i < $simulations; $i++) {
            $simPoints = $basePoints;

            foreach ($unplayedFixtures as $fixture) {
                $homeId = $fixture->home_team_id;
                $awayId = $fixture->away_team_id;


                $scores = $this->matchEngine->simulateVirtualMatch(
                    $teamsStats[$homeId],
                    $teamsStats[$awayId]
                );

                if ($scores[0] > $scores[1]) {
                    $simPoints[$homeId] += 3;
                } elseif ($scores[0] == $scores[1]) {
                    $simPoints[$homeId] += 1;
                    $simPoints[$awayId] += 1;
                } else {
                    $simPoints[$awayId] += 3;
                }
            }

            arsort($simPoints);
            $winnerId = array_key_first($simPoints);
            $winCounts[$winnerId]++;
        }

        $results = [];
        foreach ($currentTable as $team) {
            $results[] = [
                'team_name' => $team['name'],
                'percentage' => round(($winCounts[$team['id']] / $simulations) * 100)
            ];
        }
        return $results;
    }
}
