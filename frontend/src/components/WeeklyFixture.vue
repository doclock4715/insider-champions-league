<template>
  <div class="card fixture-card">
    <h3>Week {{ week }}</h3>
    <div v-for="m in matches" :key="m.id" class="match-row">
      <div class="team home-team">{{ m.home_team.name }}</div>

      <!-- Türkçe yorum: Maç oynanmadıysa sadece 'v' yaz, oynandıysa Input kutularını göster -->
      <div class="score-area" v-if="!m.is_played">
        <span class="vs">v</span>
      </div>

      <div class="score-area editable" v-else>
        <!-- Türkçe yorum: Değerler değiştiğinde (blur veya enter) API'ye istek atacak fonksiyonu çağırıyoruz -->
        <input
          type="number"
          min="0"
          v-model="m.home_score"
          @change="updateScore(m)"
          class="score-input"
        />
        <span class="divider">-</span>
        <input
          type="number"
          min="0"
          v-model="m.away_score"
          @change="updateScore(m)"
          class="score-input"
        />
      </div>

      <div class="team away-team">{{ m.away_team.name }}</div>
    </div>
  </div>
</template>

<script setup>
import axios from "axios";

// Türkçe yorum: Ana App.vue'ye veri değiştiğini haber vermek için 'emit' kullanıyoruz
const emit = defineEmits(["score-updated"]);
const props = defineProps(["matches", "week"]);

const updateScore = async (match) => {
  try {
    // Türkçe yorum: Kullanıcı inputu değiştirdiği an Backend'e yeni skoru yolluyoruz
    const res = await axios.put(`http://localhost/api/matches/${match.id}`, {
      home_score: match.home_score,
      away_score: match.away_score,
    });

    // Türkçe yorum: Backend başarıyla yeni tabloyu hesapladıysa, Ana sayfaya 'Veriyi yenile' diyoruz
    emit("score-updated", res.data);
  } catch (error) {
    console.error("Score update failed:", error);
  }
};
</script>

<style scoped>
.fixture-card {
  padding: 15px;
}
.match-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid #f0f0f0;
}
.team {
  flex: 1;
  font-weight: 500;
  font-size: 0.95em;
}
.home-team {
  text-align: right;
  padding-right: 15px;
}
.away-team {
  text-align: left;
  padding-left: 15px;
}
.score-area {
  flex: 0 0 80px;
  text-align: center;
  display: flex;
  justify-content: center;
  align-items: center;
}
.vs {
  color: #999;
  font-weight: bold;
}
.score-input {
  width: 35px;
  height: 35px;
  text-align: center;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-weight: bold;
  font-size: 1.1em;
  outline: none;
}
.score-input:focus {
  border-color: #3498db;
  box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
}
.divider {
  margin: 0 5px;
  color: #666;
  font-weight: bold;
}
/* Chrome, Safari, Edge, Opera için Input içindeki okları (spinner) gizleme */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>
