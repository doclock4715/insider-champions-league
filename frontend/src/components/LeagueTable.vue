<template>
  <div class="card table-card">
    <h3>League Table</h3>
    <table class="table">
      <thead>
        <tr>
          <th>Team</th>
          <th title="Played">P</th>
          <th title="Won">W</th>
          <th title="Drawn">D</th>
          <th title="Lost">L</th>
          <th title="Goals For (Atılan)">GF</th>
          <th title="Goals Against (Yenilen)">GA</th>
          <th title="Goal Difference (Averaj)">GD</th>
          <th title="Points">Pts</th>
          <th title="Son 5 Maçın Formu">Form</th>
        </tr>
      </thead>
      <TransitionGroup name="list" tag="tbody">
        <tr v-for="t in table" :key="t.id">
          <td class="team-name">{{ t.name }}</td>
          <td>{{ t.p }}</td>
          <td>{{ t.w }}</td>
          <td>{{ t.d }}</td>
          <td>{{ t.l }}</td>
          <td>{{ t.gf }}</td>
          <td>{{ t.ga }}</td>
          <td>{{ t.gd > 0 ? "+" + t.gd : t.gd }}</td>
          <td class="points">{{ t.pts }}</td>

          <td class="form-cell">
            <div class="form-container">
              <!-- Hiç maç oynanmadıysa boş kalır. Oynandıysa W/D/L basılır. -->
              <span
                v-for="(result, index) in t.recent_form"
                :key="index"
                class="form-dot"
                :class="getFormDotClass(result)"
                :title="
                  result === 'W' ? 'Win' : result === 'L' ? 'Loss' : 'Draw'
                "
              >
                {{ result }}
              </span>
            </div>
          </td>
        </tr>
      </TransitionGroup>
    </table>
  </div>
</template>

<script setup>
defineProps(["table"]);

const getFormDotClass = (result) => {
  if (result === "W") return "dot-win";
  if (result === "L") return "dot-loss";
  return "dot-draw";
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

.form-cell {
  min-width: 120px;
}
.form-container {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 5px; /* Noktalar arası boşluk */
  height: 100%;
}
.form-dot {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 22px;
  height: 22px;
  border-radius: 50%; /* Tam yuvarlak yapar */
  font-size: 0.75em;
  font-weight: bold;
  color: white;
  user-select: none; /* Mouse ile metin seçilmesin */
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); /* Hafif gölge */
}

/* Premier Lig tarzı renkler */
.dot-win {
  background-color: #2ecc71;
} /* Yeşil */
.dot-loss {
  background-color: #e74c3c;
} /* Kırmızı */
.dot-draw {
  background-color: #95a5a6;
} /* Gri */

/* Türkçe yorum: Vue Transition CSS sınıfları (Sıralama Animasyonu) */
.list-move,
.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
.list-leave-active {
  position: absolute;
}
</style>
