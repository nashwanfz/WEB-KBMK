<template>
  <div class="schedule-container">
    <h1>Jadwal Kegiatan KBMK</h1>
    
    <div class="calendar-header">
      <button @click="changeMonth(-1)">&lt;</button>
      <h2>{{ monthName }} {{ year }}</h2>
      <button @click="changeMonth(1)">&gt;</button>
    </div>

    <div class="calendar-weekdays">
      <div v-for="day in weekdays" :key="day">{{ day }}</div>
    </div>

    <div class="calendar-grid">
      <div
        v-for="(day, index) in calendarGrid"
        :key="index"
        :class="['calendar-day', { 'empty': day.empty, 'has-event': day.events && day.events.length > 0 }]"
      >
        <span v-if="!day.empty">{{ day.date }}</span>

        <div v-if="day.events && day.events.length > 0" class="events-list">
          <div
            v-for="event in day.events"
            :key="event.id"
            class="event-item"
            @click="openModal(event)"
          >
            {{ event.nama }}
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Detail -->
    <div v-if="selectedActivity" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <button @click="closeModal" class="close-button">&times;</button>
        <h2>{{ selectedActivity.nama }}</h2>
        <div class="modal-meta">
          <span>📅 {{ selectedActivity.tanggal }}</span>
        </div>
        <p class="modal-description">{{ selectedActivity.deskripsi }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

// ======= DATA DASAR =======
const activities = ref([])
const weekdays = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']
const currentDate = ref(new Date())
const selectedActivity = ref(null)

// ======= COMPUTED =======
const year = computed(() => currentDate.value.getFullYear())
const month = computed(() => currentDate.value.getMonth())
const monthName = computed(() => currentDate.value.toLocaleString('id-ID', { month: 'long' }))

// ======= METHOD =======
const changeMonth = (direction) => {
  currentDate.value = new Date(currentDate.value.setMonth(currentDate.value.getMonth() + direction))
}

const openModal = (activity) => {
  selectedActivity.value = activity
}

const closeModal = () => {
  selectedActivity.value = null
}

// ======= AMBIL DATA DARI BACKEND =======
const fetchSchedules = async () => {
  try {
    const res = await axios.get('https://kbmk.unmul.ac.id/api/schedules')
    // pastikan struktur JSON dari backend seperti:
    // { "data": [ { id, nama, tanggal, deskripsi }, ... ] }
    activities.value = res.data.data
  } catch (error) {
    console.error('Gagal memuat jadwal:', error)
  }
}

onMounted(fetchSchedules)

// ======= GENERATE GRID KALENDER =======
const calendarGrid = computed(() => {
  const firstDayOfMonth = new Date(year.value, month.value, 1).getDay()
  const startDay = (firstDayOfMonth === 0) ? 6 : firstDayOfMonth - 1
  const daysInMonth = new Date(year.value, month.value + 1, 0).getDate()
  const grid = []

  for (let i = 0; i < startDay; i++) {
    grid.push({ empty: true })
  }

  for (let i = 1; i <= daysInMonth; i++) {
    const fullDate = `${year.value}-${String(month.value + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`
    const eventsOnDay = activities.value.filter(act => act.tanggal === fullDate)
    grid.push({ date: i, events: eventsOnDay })
  }

  return grid
})
</script>

<style scoped>
.schedule-container {
  padding: 1rem;
  max-width: 800px; 
  margin: auto; 
}
h1 {
  text-align: center;
  color: #2c3e50;
  margin-bottom: 1.5rem;
}
.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}
.calendar-header h2 {
  color: #2c3e50;
}
.calendar-header button {
  background-color: #3fa8e5;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
}

.calendar-grid, .calendar-weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 5px;
}

.calendar-weekdays div {
  text-align: center;
  font-weight: bold;
  padding: 0.5rem;
}

.calendar-day {
  border: 1px solid #eee;
  padding: 0.5rem;
  min-height: 90px; 
  background-color: #fff;
  transition: background-color 0.3s;
}
.calendar-day.empty {
  background-color: #f9f9f9;
}
.calendar-day.has-event {
  background-color: #eaf6ff;
}

.events-list {
  margin-top: 0.5rem;
}
.event-item {
  background-color: #3fa8e5;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  margin-bottom: 0.25rem;
  cursor: pointer;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.event-item:hover {
  background-color: #08619c;
}

.modal-overlay {
  position: fixed; top: 0; left: 0; width: 100%; height: 100%;
  background-color: rgba(0, 0, 0, 0.6); display: flex; justify-content: center;
  align-items: center; z-index: 1000;
}
.modal-content {
  background-color: #fff; padding: 2rem; border-radius: 8px; width: 90%;
  max-width: 600px; position: relative; max-height: 80vh; overflow-y: auto;
}
.close-button {
  position: absolute; top: 10px; right: 15px; background: none; border: none;
  font-size: 2rem; cursor: pointer; color: #777;
}
.modal-image-slider {
  width: 100%; height: 250px; object-fit: cover; border-radius: 8px; margin-bottom: 1rem;
}
.modal-meta {
  display: flex; gap: 1rem; color: #555; margin-bottom: 1rem; font-size: 0.9rem;
}
.modal-description {
  color: #333; line-height: 1.6;
}
</style>

