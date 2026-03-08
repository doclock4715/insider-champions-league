<template>
  <div class="card fixture-card">
    <h3 class="week-title">Week {{ week }}</h3>

    <div
      v-for="m in matches"
      :key="m.id"
      class="match-row"
      :class="{ 'is-played': m.is_played }"
    >
      <div class="team home-team">{{ m.home_team.name }}</div>

      <!-- Oynanmadıysa '-' gösteriyoruz. Çünkü hepsini bir kerede görüyoruz. -->
      <div class="score-area" v-if="!m.is_played">
        <span class="vs">-</span>
      </div>

      <div class="score-area editable" v-else>
        <!-- Oynandıysa, Input kutuları geliyor. -->
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

const emit = defineEmits(["score-updated"]);
const props = defineProps(["matches", "week"]);

const API_URL = import.meta.env.VITE_API_BASE_URL;

const updateScore = async (match) => {
  try {
    const res = await axios.put(`${API_URL}/matches/${match.id}`, {
      home_score: match.home_score,
      away_score: match.away_score,
    });

    emit("score-updated", res.data);
  } catch (error) {
    console.error("Score update failed:", error);
  }
};
</script>

<style scoped>
.fixture-card {
  padding: 10px 12px;
  margin-bottom: 0;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
  min-height: 90px; /* Kartlar aynı hizada kalsın diye minimum yükseklik verdik */
  display: flex; /* İçeriği dikey olarak ortalamak için */
  flex-direction: column;
  justify-content: center;
}

.week-title {
  font-size: 1em; /* Başlık küçültüldü */
  color: #2c3e50;
  margin: 0 0 6px 0;
  border-bottom: 1px solid #f1f5f9;
  padding-bottom: 4px;
  font-weight: 600;
  text-align: center; /* Başlığı ortaladık */
}

.match-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 6px 0; /* Satır aralıkları daraltıldı */
  border-bottom: 1px dashed #f1f5f9;
}
.match-row:last-child {
  border-bottom: none;
}

.match-row:not(.is-played) .team {
  color: #94a3b8;
} /* Oynanmamışsa daha soluk gri */

.team {
  flex: 1;
  font-weight: 500;
  font-size: 0.85em;
  white-space: normal; /* "nowrap" yerine "normal" yaptık */
  overflow-wrap: break-word; /* "ManchesterUnited" gibi uzun ve tek kelime varsa bile onu kırar */
  line-height: 1.2; /* Satır aralığını biraz açalım ki daha okunaklı olsun */
}
.home-team {
  text-align: right;
  padding-right: 10px;
}
.away-team {
  text-align: left;
  padding-left: 10px;
}

.score-area {
  flex: 0 0 65px; /* Skor alanı biraz daraltıldı */
  text-align: center;
  display: flex;
  justify-content: center;
  align-items: center;
}
.vs {
  color: #cbd5e1;
  font-weight: bold;
  font-size: 1em;
}

.score-input {
  width: 26px; /* Inputlar küçültüldü */
  height: 26px;
  text-align: center;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  font-weight: bold;
  font-size: 0.95em;
  outline: none;
  background-color: #f8fafc;
  padding: 0;
}
.score-input:focus {
  border-color: #3b82f6;
  background-color: #fff;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}
.divider {
  margin: 0 4px;
  color: #94a3b8;
  font-weight: bold;
  font-size: 0.9em;
}

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>
