<script setup>
const props = defineProps({
  items: {
    type: Array,
    required: true,
  },
})

const inscriptionsHeaders = [
  { title: "Id", key: "id", align: "center" },
  { title: "Curso", key: "course_name", align: "center" },
  {
    title: "Página",
    key: "web",
    align: "center",
  },
  {
    title: "Isla",
    key: "island",
    align: "center",
  },

  { title: "Nombre", key: "name", align: "center" },
  { title: "Teléfono", key: "phone", align: "center" },
  { title: "Email", key: "email", align: "center" },
  { title: "Publicidad", key: "advertising", align: "center" },
  { title: "Fecha", key: "created_at", align: "center" },
]

const modifiedRows = {
  advertising: (value) => (value ? "Si" : "No"),
  created_at: (value) => new Date(value).toLocaleDateString(),
}
</script>

<template>
  <v-data-table
    :items="props.items"
    :headers="inscriptionsHeaders"
    class="mt-2"
  >
    <template v-slot:item="{ item }">
      <tr>
        <td
          class="text-center"
          v-for="header in inscriptionsHeaders"
          :key="header.key"
        >
          <span v-if="typeof modifiedRows[header.key] === 'function'">
            {{ modifiedRows[header.key](item[header.key]) }}
          </span>
          <span v-else>{{ item[header.key] }}</span>
        </td>
      </tr>
    </template>
  </v-data-table>
</template>
