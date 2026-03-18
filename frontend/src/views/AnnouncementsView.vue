<template>
  <div>
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-3 flex-wrap gap-2">
      <div>
        <div class="text-subtitle-1 font-weight-bold">Announcements</div>
        <div class="text-caption text-medium-emphasis">
          Stay updated with your barangay news
        </div>
      </div>
    </div>

    <!-- Category filter -->
    <div class="d-flex gap-2 mb-4 overflow-x-auto pb-1">
      <v-btn
        v-for="cat in categories"
        :key="cat.value"
        :color="activeCategory === cat.value ? 'primary' : 'default'"
        :variant="activeCategory === cat.value ? 'flat' : 'tonal'"
        rounded="xl"
        size="x-small"
        :prepend-icon="cat.icon"
        @click="filterCategory(cat.value)"
      >
        {{ cat.label }}
      </v-btn>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="d-flex justify-center py-10">
      <v-progress-circular indeterminate color="primary" size="36"/>
    </div>

    <!-- Empty state -->
    <div v-else-if="filtered.length === 0" class="text-center py-10">
      <v-icon size="52" color="grey-lighten-2">mdi-bullhorn-outline</v-icon>
      <div class="text-body-2 font-weight-medium mt-3">No announcements yet</div>
      <div class="text-caption text-medium-emphasis mt-1">
        Check back later for updates from your barangay
      </div>
    </div>

    <!-- Announcements list -->
    <div v-else>
      <!-- Pinned -->
      <div v-if="pinned.length" class="mb-2">
        <div class="text-caption font-weight-bold text-medium-emphasis mb-2 d-flex align-center gap-1">
          <v-icon size="12">mdi-pin</v-icon> PINNED
        </div>
        <v-card
          v-for="item in pinned"
          :key="item.id"
          rounded="xl"
          flat
          border
          class="mb-2 announcement-card pinned-card"
          @click="openAnnouncement(item)"
        >
          <v-card-text class="pa-3">
            <div class="d-flex align-start gap-2">
              <v-avatar :color="categoryBg(item.category)" size="36" rounded="lg">
                <v-icon :color="categoryColor(item.category)" size="18">
                  {{ item.category_icon }}
                </v-icon>
              </v-avatar>
              <div class="flex-1 min-width-0">
                <div class="d-flex align-center gap-1 mb-1 flex-wrap">
                  <v-chip :color="item.priority_color" size="x-small" variant="flat">
                    {{ item.priority.toUpperCase() }}
                  </v-chip>
                  <v-chip size="x-small" variant="tonal" color="grey">
                    {{ item.category }}
                  </v-chip>
                </div>
                <div class="text-caption font-weight-bold mb-1">{{ item.title }}</div>
                <div class="text-caption text-medium-emphasis text-truncate-2" style="font-size:11px">
                  {{ item.content }}
                </div>
                <div class="text-caption text-medium-emphasis mt-1" style="font-size:10px">
                  <v-icon size="10" class="mr-1">mdi-account-outline</v-icon>
                  {{ item.author }} ·
                  <v-icon size="10" class="mr-1">mdi-clock-outline</v-icon>
                  {{ item.time_ago }}
                </div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </div>

      <!-- Unpinned -->
      <div class="text-caption font-weight-bold text-medium-emphasis mb-2" v-if="unpinned.length">
        LATEST UPDATES
      </div>
      <v-card
        v-for="item in unpinned"
        :key="item.id"
        rounded="xl"
        flat
        border
        class="mb-2 announcement-card"
        @click="openAnnouncement(item)"
      >
        <v-card-text class="pa-3">
          <div class="d-flex align-start gap-2">
            <v-avatar :color="categoryBg(item.category)" size="34" rounded="lg">
              <v-icon :color="categoryColor(item.category)" size="16">
                {{ item.category_icon }}
              </v-icon>
            </v-avatar>
            <div class="flex-1 min-width-0">
              <div class="d-flex align-center gap-1 mb-1 flex-wrap">
                <v-chip
                  v-if="item.priority !== 'normal'"
                  :color="item.priority_color"
                  size="x-small"
                  variant="tonal"
                >
                  {{ item.priority.toUpperCase() }}
                </v-chip>
                <v-chip size="x-small" variant="tonal" color="grey">{{ item.category }}</v-chip>
              </div>
              <div class="text-caption font-weight-bold mb-1">{{ item.title }}</div>
              <div class="text-caption text-medium-emphasis text-truncate-2" style="font-size:11px">
                {{ item.content }}
              </div>
              <div class="text-caption text-medium-emphasis mt-1" style="font-size:10px">
                <v-icon size="10" class="mr-1">mdi-account-outline</v-icon>
                {{ item.author }} ·
                <v-icon size="10" class="mr-1">mdi-clock-outline</v-icon>
                {{ item.time_ago }}
              </div>
            </div>
            <v-icon size="16" color="grey-lighten-1">mdi-chevron-right</v-icon>
          </div>
        </v-card-text>
      </v-card>
    </div>

    <!-- Detail Dialog -->
    <v-dialog v-model="dialog" max-width="520" scrollable>
      <v-card v-if="selected" rounded="xl" elevation="0" border>
        <v-card-title class="pa-4 pb-2">
          <div class="d-flex align-start gap-2">
            <v-avatar :color="categoryBg(selected.category)" size="40" rounded="lg">
              <v-icon :color="categoryColor(selected.category)" size="22">
                {{ selected.category_icon }}
              </v-icon>
            </v-avatar>
            <div class="flex-1">
              <div class="d-flex gap-2 mb-1 flex-wrap">
                <v-chip :color="selected.priority_color" size="x-small" variant="flat">
                  {{ selected.priority.toUpperCase() }}
                </v-chip>
                <v-chip size="x-small" variant="tonal" color="grey">
                  {{ selected.category }}
                </v-chip>
              </div>
              <div class="text-body-2 font-weight-bold">{{ selected.title }}</div>
            </div>
          </div>
        </v-card-title>

        <v-divider/>

        <v-card-text class="pa-4">
          <div class="text-caption mb-3" style="line-height:1.7;white-space:pre-line">
            {{ selected.content }}
          </div>
          <div class="text-caption text-medium-emphasis" style="font-size:10px">
            <v-icon size="12" class="mr-1">mdi-account-outline</v-icon>
            Posted by {{ selected.author }}
            <span class="mx-2">·</span>
            <v-icon size="12" class="mr-1">mdi-calendar-outline</v-icon>
            {{ selected.date }}
          </div>
        </v-card-text>

        <v-card-actions class="pa-3 pt-0">
          <v-spacer/>
          <v-btn rounded="lg" size="small" variant="tonal" @click="dialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'

const loading        = ref(false)
const announcements  = ref([])
const activeCategory = ref('all')
const dialog         = ref(false)
const selected       = ref(null)

const categories = [
  { value: 'all',            label: 'All',            icon: 'mdi-view-list'        },
  { value: 'emergency',      label: 'Emergency',      icon: 'mdi-alert-circle'      },
  { value: 'health',         label: 'Health',         icon: 'mdi-medical-bag'       },
  { value: 'event',          label: 'Events',         icon: 'mdi-calendar-star'     },
  { value: 'infrastructure', label: 'Infrastructure', icon: 'mdi-road-variant'      },
  { value: 'livelihood',     label: 'Livelihood',     icon: 'mdi-briefcase-outline' },
]

const filtered = computed(() => {
  if (activeCategory.value === 'all') return announcements.value
  return announcements.value.filter(a => a.category === activeCategory.value)
})

const pinned   = computed(() => filtered.value.filter(a => a.is_pinned))
const unpinned = computed(() => filtered.value.filter(a => !a.is_pinned))

function categoryColor(cat) {
  const map = {
    emergency: 'error', health: 'success', event: 'purple',
    infrastructure: 'warning', livelihood: 'teal', general: 'primary',
  }
  return map[cat] || 'primary'
}

function categoryBg(cat) {
  const map = {
    emergency: 'red-lighten-5', health: 'green-lighten-5', event: 'purple-lighten-5',
    infrastructure: 'orange-lighten-5', livelihood: 'teal-lighten-5', general: 'blue-lighten-5',
  }
  return map[cat] || 'blue-lighten-5'
}

function filterCategory(cat) { activeCategory.value = cat }

function openAnnouncement(item) {
  selected.value = item
  dialog.value   = true
}

async function fetchAnnouncements() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/announcements')
    announcements.value = data.announcements
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchAnnouncements)
</script>

<style scoped>
.announcement-card {
  cursor: pointer;
  transition: transform 0.15s, box-shadow 0.15s;
}
.announcement-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.08) !important;
}
.pinned-card { border-left: 3px solid #1A237E !important; }
.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>