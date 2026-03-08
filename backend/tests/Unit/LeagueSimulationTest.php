<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Team;
use App\Models\Fixture;
use App\Services\LeagueService;
use App\Services\MatchEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase; // Her testten önce veritabanını sıfırlamak için

class LeagueSimulationTest extends TestCase
{
    // Bu trait sayesinde her test sıfır, tertemiz bir veritabanıyla başlar ve bittiğinde silinir.
    use RefreshDatabase;

    private LeagueService $leagueService;
    private MatchEngineService $matchEngine;

    // Test başlamadan önce gerekli ortamı (servisleri) hazırlıyoruz.
    protected function setUp(): void
    {
        parent::setUp();

        $this->matchEngine = app(MatchEngineService::class);
        $this->leagueService = app(LeagueService::class);

        // Kendi yazdığımız Seeder'ı çalıştırarak 4 takımı ve 12 maçlık fikstürü veritabanına ekliyoruz.
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    /**
     * Test 1: Fikstür Doğru Oluşuyor Mu?
     * Veritabanında tam olarak 4 takım ve 12 oynanmamış maç olmalı.
     */
    public function test_fixture_generation_creates_correct_amount_of_matches(): void
    {
        $this->assertDatabaseCount('teams', 4); // 4 takım olmalı
        $this->assertDatabaseCount('fixtures', 12); // Toplam 12 maç olmalı

        // Başlangıçta hiçbir maç oynanmamış olmalı (is_played = 0)
        $unplayedMatches = Fixture::where('is_played', false)->count();
        $this->assertEquals(12, $unplayedMatches);
    }

    /**
     * Test 2: Puan Tablosu Galibiyeti Doğru Hesaplıyor Mu?
     * Bir maçı manuel sonuçlandırıp, kazananın 3 puan aldığını doğrulayacağız.
     */
    public function test_league_table_calculates_points_correctly_for_a_win(): void
    {
        // 1. haftanın ilk maçını al (Örn: City vs Arsenal)
        $match = Fixture::where('week', 1)->first();

        $homeTeamId = $match->home_team_id;

        // Maçı Ev Sahibi takıma 3-0 kazandır ve kaydet
        $match->update([
            'home_score' => 3,
            'away_score' => 0,
            'is_played' => true
        ]);

        // Tabloyu hesaplat
        $table = $this->leagueService->getTable();

        // Ev sahibi takımı tablodan bul
        $winnerStats = $table->firstWhere('id', $homeTeamId);

        // Doğrulamalar (Assertions)
        $this->assertEquals(3, $winnerStats['pts'], 'Kazanan takım 3 puan almalıdır.');
        $this->assertEquals(1, $winnerStats['w'], 'Kazanan takımın 1 galibiyeti olmalıdır.');
        $this->assertEquals(3, $winnerStats['gd'], 'Averaj (Attığı - Yediği) 3 olmalıdır.');
    }

    /**
     * Test 3: MatchEngine (Simülasyon Motoru) Skor Üretiyor Mu?
     * Motor çalıştırıldığında maçın skorları Null (boş) kalmamalı.
     */
    public function test_match_engine_generates_valid_scores_and_updates_stats(): void
    {
        // Oynanmamış bir maç al
        $match = Fixture::where('is_played', false)->first();

        $homeTeam = $match->homeTeam;
        $initialForm = $homeTeam->form;

        // Simülasyonu çalıştır
        $this->matchEngine->simulate($match);

        // Doğrulamalar
        $this->assertTrue($match->is_played, 'Maç oynandı olarak işaretlenmelidir.');
        $this->assertNotNull($match->home_score, 'Ev sahibi skoru boş olmamalıdır.');
        $this->assertNotNull($match->away_score, 'Deplasman skoru boş olmamalıdır.');
    }

    /**
     * Test 4: Monte Carlo Simülasyonu Doğru Tahmin Üretiyor mu?
     * Eğer ligin bitimine 1 hafta kaldıysa ve bir takım açık ara şampiyonsa,
     * sistem o takıma %100 şampiyonluk ihtimali vermelidir.
     */
    public function test_monte_carlo_predictions_for_guaranteed_champion(): void
    {
        // 1. Tüm maçları ev sahibi (ilk takım) kazanmış gibi ayarlayalım (5 hafta boyunca)
        $teamId = Team::first()->id;

        $matches = Fixture::where('week', '<=', 5)->get();
        foreach ($matches as $match) {
            if ($match->home_team_id == $teamId) {
                $match->update(['home_score' => 5, 'away_score' => 0, 'is_played' => true]);
            } elseif ($match->away_team_id == $teamId) {
                $match->update(['home_score' => 0, 'away_score' => 5, 'is_played' => true]);
            } else {
                $match->update(['home_score' => 1, 'away_score' => 1, 'is_played' => true]); // Diğerleri berabere kalsın
            }
        }

        // 2. Tabloyu al ve tahminleri çalıştır
        $table = $this->leagueService->getTable();
        $predictions = $this->leagueService->getPredictions($table);

        // 3. Birinci takımın şampiyonluk yüzdesini bul
        $championPrediction = collect($predictions)->firstWhere('team_name', Team::find($teamId)->name);

        // 4. Doğrulama: Puan farkı çok açıldığı için %100 şampiyon çıkmalı (veya ona çok yakın bir değer)
        $this->assertEquals(100, $championPrediction['percentage'], 'Açık ara lider olan takımın şampiyonluk ihtimali %100 olmalıdır.');
    }

    /**
     * Test 5: Manuel Skor Düzenleme Tabloyu Anında Etkiliyor mu?
     * API'ye (veya sisteme) skor değişikliği geldiğinde averajların/puanların bozulmadan değiştiğini doğruluyoruz.
     */
    public function test_manual_score_edit_updates_table_correctly(): void
    {
        // 1. Bir maç oynat
        $match = Fixture::first();
        $match->update(['home_score' => 2, 'away_score' => 0, 'is_played' => true]);

        // 2. Ev sahibinin ilk averajını (GD) kontrol et
        $tableBefore = $this->leagueService->getTable();
        $gdBefore = $tableBefore->firstWhere('id', $match->home_team_id)['gd'];
        $this->assertEquals(2, $gdBefore); // Averaj 2 olmalı

        // 3. Skoru manuel olarak 5-0 yap (Frontend'den Input ile gelmiş gibi)
        $match->update(['home_score' => 5, 'away_score' => 0]);

        // 4. Tabloyu tekrar çek
        $tableAfter = $this->leagueService->getTable();
        $gdAfter = $tableAfter->firstWhere('id', $match->home_team_id)['gd'];

        // 5. Doğrulama: Yeni averaj 5 olmalı, eski veriler üzerine eklenmemeli! (Single Source of Truth)
        $this->assertEquals(5, $gdAfter, 'Skor manuel değiştiğinde averaj baştan doğru hesaplanmalıdır.');
    }

    /**
     * Test 6: Şans ve Form Faktörleri Sınırların Dışına Çıkmıyor Mu?
     * MatchEngine'deki Bayesian güncelleme matematiğinin "sonsuzluğa" gitmediğinden emin oluyoruz.
     */
    public function test_team_stats_stay_within_logical_limits_after_extreme_match(): void
    {
        $match = Fixture::first();
        $homeTeam = $match->homeTeam;

        // Çok ekstrem bir skor yazalım (10-0 gibi)
        $match->update(['home_score' => 10, 'away_score' => 0, 'is_played' => true]);

        // Bu skora göre takımı manuel güncelleyelim (Sanki motor çalışmış gibi)
        // Lambda (Beklenti) 1 olsun diyelim.
        $reflection = new \ReflectionClass($this->matchEngine);
        $method = $reflection->getMethod('updateTeamStats');
        $method->setAccessible(true);

        $method->invokeArgs($this->matchEngine, [$homeTeam, true, 10, 0, 1.0]);

        // Takımı tekrar yükle
        $homeTeam->refresh();

        // Doğrulamalar
        $this->assertTrue($homeTeam->form <= 10, 'Takım formu 10\'u geçmemelidir.');
        $this->assertTrue($homeTeam->attack_strength <= 2.0, 'Hücum gücü 2.0 limitini (clamping) aşmamalıdır.');
    }
}
