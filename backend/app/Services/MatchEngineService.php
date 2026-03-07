<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\Team;

class MatchEngineService
{
    private const LEAGUE_AVG_GOALS = 1.35; // Average goals per team per match
    private const HOME_ADVANTAGE = 1.15; // Home teams are 15% stronger
    private const FORM_IMPACT = 0.02; // Each form point affects strength by 2%
    private const STAT_UPDATE_RATE = 0.05; // Learning rate for Bayesian-like updates

    public function simulate(Fixture $fixture): void
    {
        $home = $fixture->homeTeam;
        $away = $fixture->awayTeam;

        
        // Get virtual scores without saving to DB
        $scores = $this->simulateVirtualMatch($home, $away);
        $homeScore = $scores[0];
        $awayScore = $scores[1];

        // Update fixture with the result
        $fixture->update([
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'is_played' => true,
        ]);

        // Update team stats post-match
        $this->updateTeamStats($home, $homeScore > $awayScore, $homeScore, $awayScore, $scores['lambdaHome']);
        $this->updateTeamStats($away, $awayScore > $homeScore, $awayScore, $homeScore, $scores['lambdaAway']);
    }

    
    /**
     * Simulates a match purely in memory (Returns scores and lambdas).
     * Used heavily by Monte Carlo to avoid DB crashes.
     */
    public function simulateVirtualMatch($home, $away): array
    {
        // Gelen veri nesne (Team) ise onu array'e çevir
        $hStats = is_array($home) ? $home : ['attack_strength' => $home->attack_strength, 'defense_strength' => $home->defense_strength, 'form' => $home->form];
        $aStats = is_array($away) ? $away : ['attack_strength' => $away->attack_strength, 'defense_strength' => $away->defense_strength, 'form' => $away->form];

        $luckHome = 1 + (rand(-5, 5) / 100);
        $luckAway = 1 + (rand(-5, 5) / 100);

        
        $formAdjustmentHome = 1 + ($hStats['form'] * self::FORM_IMPACT);
        $formAdjustmentAway = 1 + ($aStats['form'] * self::FORM_IMPACT);

        $lambdaHome = self::LEAGUE_AVG_GOALS * $hStats['attack_strength'] * $aStats['defense_strength'] * self::HOME_ADVANTAGE * $formAdjustmentHome * $luckHome;
        $lambdaAway = self::LEAGUE_AVG_GOALS * $aStats['attack_strength'] * $hStats['defense_strength'] * $formAdjustmentAway * $luckAway;

        return [
            $this->generatePoissonGoals($lambdaHome),
            $this->generatePoissonGoals($lambdaAway),
            'lambdaHome' => $lambdaHome,
            'lambdaAway' => $lambdaAway
        ];
    }
    /**
     * Generates a random number from a Poisson distribution.
     */
    private function generatePoissonGoals(float $lambda): int
    {
        $L = exp(-$lambda);
        $k = 0;
        $p = 1.0;
        do {
            $k++;
            $p *= (float)rand() / (float)getrandmax();
        } while ($p > $L);

        return $k - 1;
    }

    /**
     * Update team's form and attack/defense strength after a match.
     */
    private function updateTeamStats(Team $team, bool $isWinner, int $goalsFor, int $goalsAgainst, float $lambdaFor): void
    {
        // Update form
        $newForm = $team->form + ($isWinner ? 2 : -2);
        $team->form = max(-10, min(10, $newForm)); // Cap form between -10 and +10

        // Update attack and defense strengths (Bayesian-like)
        
        // Calculate the update values
        $attackUpdate = self::STAT_UPDATE_RATE * ($goalsFor - $lambdaFor);
        
        $defenseUpdate = self::STAT_UPDATE_RATE * ($goalsAgainst - $lambdaFor);

        // Apply updates with caps to prevent infinity values
        
        $team->attack_strength = max(0.5, min(2.0, $team->attack_strength + $attackUpdate));
        $team->defense_strength = max(0.5, min(2.0, $team->defense_strength + $defenseUpdate));

        $team->save();
    }
}
