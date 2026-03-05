<template>
  <div class="container">
    <h1>Insider Champions League</h1>
    <div class="grid">
      <LeagueTable :table="data.table" />
      <div class="side">
        <WeeklyFixture
          v-if="data.fixtures[data.current_week + 1]"
          :matches="data.fixtures[data.current_week + 1]"
          :week="data.current_week + 1"
        />

        <div v-if="data.current_week >= 4" class="card">
          <h3>Championship Predictions</h3>
          <div v-for="p in data.predictions" :key="p.team_name">
            {{ p.team_name }}: %{{ p.percentage }}
          </div>
        </div>
      </div>
    </div>
    <div class="actions">
      <button @click="playNext" :disabled="data.current_week >= 6">
        Next Week
      </button>
      <button @click="playAll" :disabled="data.current_week >= 6">
        Play All
      </button>
      <button @click="reset">Reset League</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import LeagueTable from "./components/LeagueTable.vue";
import WeeklyFixture from "./components/WeeklyFixture.vue";

const data = ref({ table: [], fixtures: {}, current_week: 0, predictions: [] });

const load = async () => {
  const res = await axios.get("http://localhost/api/status");
  data.value = res.data;
};

const playNext = async () => {
  const res = await axios.post("http://localhost/api/play-week");
  data.value = res.data;
};

const playAll = async () => {
  const res = await axios.post("http://localhost/api/play-all");
  data.value = res.data;
};

const reset = async () => {
  const res = await axios.post("http://localhost/api/reset");
  data.value = res.data;
};

onMounted(load);
</script>

<style>
.container {
  max-width: 1000px;
  margin: auto;
  font-family: sans-serif;
}
.grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 20px;
}
.card {
  border: 1px solid #ddd;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 20px;
}
table {
  width: 100%;
  text-align: left;
  border-collapse: collapse;
}
th,
td {
  padding: 8px;
  border-bottom: 1px solid #eee;
}
.match {
  display: flex;
  justify-content: space-between;
  padding: 5px 0;
}
.score {
  font-weight: bold;
}
.actions {
  margin-top: 20px;
}
button {
  padding: 10px 20px;
  cursor: pointer;
  margin-right: 10px;
}
</style>
