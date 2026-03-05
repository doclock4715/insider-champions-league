<template>
  <div class="card">
    <h3>League Table</h3>
    <table>
      <thead>
        <tr>
          <th>Team</th>
          <th>P</th>
          <th>W</th>
          <th>D</th>
          <th>L</th>
          <th>GD</th>
          <th>Pts</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="t in table" :key="t.id">
          <td>{{ t.name }}</td>
          <td>{{ t.p }}</td>
          <td>{{ t.w }}</td>
          <td>{{ t.d }}</td>
          <td>{{ t.l }}</td>
          <td>{{ t.gd }}</td>
          <td>{{ t.pts }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
<script setup>
defineProps(["table"]);
</script>
