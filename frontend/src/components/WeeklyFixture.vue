<template>
  <div class="card">
    <h3>Week {{ week }} Results</h3>
    <div v-for="m in matches" :key="m.id" class="match">
      <span>{{ m.home_team.name }}</span>
      <span class="score">{{
        m.is_played ? m.home_score + " - " + m.away_score : "v"
      }}</span>
      <span>{{ m.away_team.name }}</span>
    </div>
  </div>
</template>
<script setup>
defineProps(["matches", "week"]);
</script>
