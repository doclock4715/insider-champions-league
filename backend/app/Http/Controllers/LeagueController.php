<?php

namespace App\Http\Controllers;

use App\Models\{Team, Fixture};
use App\Services\LeagueService;
use Illuminate\Http\Request;
use App\Services\MatchEngineService; // Türkçe yorum: bak burası değişti
use Illuminate\Support\Facades\Artisan;

class LeagueController extends Controller
{
    protected $leagueService;
    protected $matchEngine; // Türkçe yorum: bak burası değişti

    public function __construct(LeagueService $ls, MatchEngineService $me)
    { // Türkçe yorum: bak burası değişti
        $this->leagueService = $ls;
        $this->matchEngine = $me; // Türkçe yorum: bak burası değişti
    }

    public function getStatus()
    {
        $table = $this->leagueService->getTable();
        return response()->json([
            'table' => $table,
            'fixtures' => Fixture::with(['homeTeam', 'awayTeam'])->get()->groupBy('week'),
            'current_week' => Fixture::where('is_played', true)->max('week') ?? 0,
            'predictions' => $this->leagueService->getPredictions($table)
        ]);
    }

    public function playWeek()
    { // Türkçe yorum: bak burası değişti
        $nextWeek = (Fixture::where('is_played', true)->max('week') ?? 0) + 1;

        if ($nextWeek > 6) { // Türkçe yorum: Lig bittiyse daha fazla oynatma
            return $this->getStatus();
        }

        $matches = Fixture::where('week', $nextWeek)->get();
        foreach ($matches as $match) {
            $this->matchEngine->simulate($match); // Türkçe yorum: Artık tüm mantık burada
        }
        return $this->getStatus();
    }

    // Türkçe yorum: blok eklendi
    public function playAllWeeks()
    {
        $matchEngine = new MatchEngineService();

        // Türkçe yorum: Oynanmamış tüm maçları bul ve sırayla oyna
        $unplayedMatches = Fixture::where('is_played', false)->orderBy('week')->get();

        foreach ($unplayedMatches as $match) {
            // Türkçe yorum: Takımların son güncel halini almak için yeniden yüklüyoruz
            $match->setRelation('homeTeam', $match->homeTeam()->first());
            $match->setRelation('awayTeam', $match->awayTeam()->first());

            $matchEngine->simulate($match);
        }

        return $this->getStatus();
    }

    // Türkçe yorum: bak burası değişti
    public function reset()
    {
        // Türkçe yorum: Seeder komutunu programatik olarak çalıştırıyoruz.
        // Bu, takımların attack_strength vb. özelliklerini ilk haline geri döndürür.
        Artisan::call('db:seed', [
            '--class' => 'DatabaseSeeder',
            '--force' => true // Prod ortamında sormaması için
        ]);

        return $this->getStatus();
    }

    public function updateMatch(Request $request, $id)
    {
        // Gelen id'ye ait maçı bul
        $match = Fixture::findOrFail($id);

        // Sadece oynanmış (is_played = true) maçların skorları düzenlenebilir
        if ($match->is_played) {
            $match->update([
                'home_score' => $request->home_score,
                'away_score' => $request->away_score
            ]);

            // Not: İstenirse burada MatchEngine'deki updateTeamStats() yeniden çağrılabilir 
            // ama manuel düzenlemelerde takım formunun bozulmaması için çağırmıyoruz.
        }

        // Skor değiştiğine göre LeagueService tabloyu yeniden hesaplayacak
        return $this->getStatus();
    }
}
