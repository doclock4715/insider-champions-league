<template>
  <div class="container">
    <h1>Insider Champions League</h1>

    <div class="actions top-actions">
      <button
        class="btn primary"
        @click="playNext"
        :disabled="isLoading || data.current_week >= 6"
      >
        <span v-if="isLoading" class="btn-spinner"></span>
        <span v-else>Play Next Week</span>
      </button>

      <button
        class="btn success"
        @click="playAll"
        :disabled="isLoading || data.current_week >= 6"
      >
        <span v-if="isLoading" class="btn-spinner"></span>
        <span v-else>Play All</span>
      </button>

      <button class="btn danger" @click="reset" :disabled="isLoading">
        <span v-if="isLoading" class="btn-spinner"></span>
        <span v-else>Reset League</span>
      </button>
    </div>

    <div class="grid">
      <div class="main-column">
        <LeagueTable :table="data.table" />

        <div v-if="data.current_week >= 4" class="card prediction-card">
          <h3>Championship Predictions (Week {{ data.current_week }})</h3>
          <div
            v-for="p in data.predictions"
            :key="p.team_name"
            class="prediction-row"
          >
            <span class="team-name">{{ p.team_name }}</span>
            <div class="progress-bar-container">
              <div
                class="progress-bar"
                :style="{ width: p.percentage + '%' }"
              ></div>
            </div>
            <span class="percentage">%{{ p.percentage }}</span>
          </div>
        </div>
      </div>

      <div class="side-column">
        <h3>All Fixtures & Results</h3>
        <div class="fixtures-grid">
          <WeeklyFixture
            v-for="item in allFixtures"
            :key="item.week"
            :matches="item.matches"
            :week="item.week"
            @score-updated="handleScoreUpdated"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import LeagueTable from "./components/LeagueTable.vue";
import WeeklyFixture from "./components/WeeklyFixture.vue";

const data = ref({ table: [], fixtures: {}, current_week: 0, predictions: [] });
const isLoading = ref(false);

const allFixtures = computed(() => {
  if (!data.value.fixtures) return [];

  // Anahtarları (1, 2, 3...) bul ve küçükten büyüğe sırala
  const weeks = Object.keys(data.value.fixtures).sort((a, b) => a - b);

  const sortedArr = [];
  weeks.forEach((weekStr) => {
    sortedArr.push({ week: weekStr, matches: data.value.fixtures[weekStr] });
  });

  return sortedArr;
});

const withLoading = async (apiCall) => {
  isLoading.value = true;
  try {
    const res = await apiCall();
    data.value = res.data;
  } catch (error) {
    console.error("API Error:", error);
  } finally {
    isLoading.value = false;
  }
};

const API_URL = import.meta.env.VITE_API_BASE_URL;

const load = () => withLoading(() => axios.get(`${API_URL}/status`));
const playNext = () => withLoading(() => axios.post(`${API_URL}/play-week`));
const playAll = () => withLoading(() => axios.post(`${API_URL}/play-all`));
const reset = () => withLoading(() => axios.post(`${API_URL}/reset`));

const handleScoreUpdated = (newData) => {
  data.value = { ...newData, table: [...newData.table] };
};

onMounted(load);
</script>

<style>
body {
  background-color: #f4f7f6;
  color: #333;
  margin: 0;
  padding: 20px;
}
.container {
  max-width: 1200px;
  margin: auto;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}
h1 {
  text-align: center;
  color: #2c3e50;
  margin-bottom: 30px;
}

.grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 25px;
  align-items: start;
}
.card {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  margin-bottom: 20px;
}
h3 {
  margin-top: 0;
  color: #34495e;
  border-bottom: 2px solid #ecf0f1;
  padding-bottom: 10px;
}

.top-actions {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-bottom: 30px;
}
.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.2s;
  font-size: 1em;
  color: white;
}
.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.btn.primary {
  background-color: #3498db;
}
.btn.primary:hover:not(:disabled) {
  background-color: #2980b9;
}
.btn.success {
  background-color: #2ecc71;
}
.btn.success:hover:not(:disabled) {
  background-color: #27ae60;
}
.btn.danger {
  background-color: #e74c3c;
}
.btn.danger:hover:not(:disabled) {
  background-color: #c0392b;
}

/* Prediction Bar CSS */
.prediction-row {
  display: flex;
  align-items: center;
  margin-bottom: 12px;
}
.prediction-row .team-name {
  width: 140px;
  font-weight: 500;
}
.progress-bar-container {
  flex-grow: 1;
  background-color: #ecf0f1;
  border-radius: 10px;
  height: 20px;
  margin: 0 15px;
  overflow: hidden;
}
.progress-bar {
  height: 100%;
  background-color: #3498db;
  transition: width 0.5s ease-in-out;
}
.percentage {
  width: 50px;
  text-align: right;
  font-weight: bold;
}

.fixtures-grid {
  display: grid;
  /* 2 eşit sütun oluşturur. Ekran daralırsa (örn: mobil) tek sütuna düşer. */
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 15px; /* Kartlar arası boşluk */
}

/* Ana Grid Layout'unu biraz daha genişletelim ki 2 sütun fikstür sığsın */
.grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  align-items: start;
}
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 140px; /* Buton genişliği değişip durmasın diye sabit min-width verdik */
}

.btn-spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.5);
  border-top: 2px solid white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite; /* Animasyon hızını artırdık (0.6s) */
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
