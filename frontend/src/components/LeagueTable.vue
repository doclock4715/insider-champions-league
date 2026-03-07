<template>
  <div class="card table-card">
    <h3>League Table</h3>
    <table class="table">
      <thead>
        <tr>
          <th>Team</th>
          <th>P</th>
          <th>W</th>
          <th>D</th>
          <th>L</th>
          <th>GD</th>
          <th>Pts</th>
          <th title="Güncel Form Durumu (-10 ile +10 arası)">Form</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="t in table" :key="t.id">
          <td class="team-name">{{ t.name }}</td>
          <td>{{ t.p }}</td>
          <td>{{ t.w }}</td>
          <td>{{ t.d }}</td>
          <td>{{ t.l }}</td>
          <td>{{ t.gd > 0 ? "+" + t.gd : t.gd }}</td>
          <td class="points">{{ t.pts }}</td>
          <td :class="formClass(t.form)">
            {{ t.form > 0 ? "+" + t.form : t.form }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps(["table"]);


const formClass = (form) => {
  if (form > 0) return "text-green";
  if (form < 0) return "text-red";
  return "text-gray";
};
</script>

<style scoped>
.table-card {
  overflow-x: auto;
}
.table {
  width: 100%;
  border-collapse: collapse;
  text-align: center;
}
.table th {
  background: #f8f9fa;
  padding: 12px;
  font-weight: 600;
  color: #333;
  border-bottom: 2px solid #ddd;
}
.table td {
  padding: 10px;
  border-bottom: 1px solid #eee;
}
.team-name {
  text-align: left;
  font-weight: 500;
}
.points {
  font-weight: bold;
  font-size: 1.1em;
  color: #2c3e50;
}
.text-green {
  color: #27ae60;
  font-weight: bold;
}
.text-red {
  color: #c0392b;
  font-weight: bold;
}
.text-gray {
  color: #7f8c8d;
}
</style>
