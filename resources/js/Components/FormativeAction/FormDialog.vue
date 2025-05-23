<script setup>
import InternshipDialog from "./InternshipForm.vue"
import FormativeActionForm from "./FormativeActionForm.vue"
import { ref, computed, watch } from "vue"

const props = defineProps(["show", "item", "type", "endPoint", "modalMode"])
const emit = defineEmits(["closeDialog", "reloadItems", "update:modalMode"])

const dialogState = computed({
  get: () => props.show,
  set: (value) => emit("closeDialog", value),
})
const type = ref()

watch(dialogState, (value) => {
  if (value) {
    type.value = props.type
  } else {
    emit("reloadItems")
    modalMode.value = "FORM"
  }
})

const modalMode = computed({
  get: () => props.modalMode,
  set: (value) => emit("update:modalMode", value),
})

const changeModalMode = () => {
  if (modalMode.value == "FORM") {
    modalMode.value = "PRACTS"
  } else {
    modalMode.value = "FORM"
  }
}
</script>

<template>
  <v-dialog scrollable v-model="dialogState" width="1024">
    <v-card>
      <v-card-title class="mt-1">
        <v-row class="space-between px-6 align-center">
          <v-col cols="10" class="text-left">
            <span class="text-h5"
              >{{ type === "create" ? "Crear" : "Editar" }} Acción
              Formativa</span
            >
          </v-col>
          <v-col cols="2" class="text-right">
            <v-btn
              v-if="modalMode == 'PRACTS'"
              icon
              :href="'/dashboard/internships/export-excel'"
              target="_blank"
              class="me-2"
            >
              <v-icon>mdi-file-excel-outline</v-icon>
              <v-tooltip activator="parent"
                >Exportar prácticas a Excel</v-tooltip
              >
            </v-btn>
            <v-btn
              v-if="props.type == 'edit'"
              :variant="modalMode == 'PRACTS' ? 'tonal' : 'text'"
              icon
              @click="changeModalMode"
            >
              <v-icon>mdi-briefcase-edit</v-icon>
              <v-tooltip activator="parent">{{
                modalMode == "PRACTS" ? "Ver acción formativa" : "Ver prácticas"
              }}</v-tooltip>
            </v-btn>
          </v-col>
        </v-row>
      </v-card-title>
      <v-divider></v-divider>

      <v-card-text>
        <v-container>
          <template v-if="type == 'edit' && modalMode == 'PRACTS'">
            <InternshipDialog :item="props.item"></InternshipDialog>
          </template>
          <template v-if="modalMode == 'FORM'">
            <FormativeActionForm
              :item="props.item"
              :endPoint="endPoint"
              v-model:type="type"
              :show="dialogState"
              @reloadItems="emit('reloadItems')"
              @closeDialog="dialogState = false"
            ></FormativeActionForm>
          </template>
        </v-container>
      </v-card-text>

      <v-divider></v-divider>

      <v-card-actions class="d-flex justify-center">
        <v-btn color="red-darken-1" variant="text" @click="dialogState = false">
          Cerrar
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
