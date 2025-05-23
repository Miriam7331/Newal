<script setup>
import { computed } from "vue"
import { router } from "@inertiajs/vue3"

const props = defineProps([
  "show",
  "elementName",
  "studentId",
  "formativeActionId",
])

const emit = defineEmits(["closeDialog", "reloadItems"])

const dialogState = computed({
  get: () => props.show,
  set: () => {
    emit("closeDialog")
  },
})

const submit = () => {
  router.post(
    `/dashboard/students/${props.studentId}/remove-formative-action`,
    {
      formative_actions_id: props.formativeActionId,
      origin: "formativeAction",
    },
    {
      only: ["students", "flash", "errors"],
      onSuccess: () => {
        emit("reloadItems")
        dialogState.value = false
      },
    }
  )
}
</script>

<template>
  <v-dialog scrollable v-model="dialogState" width="auto">
    <v-card>
      <v-card-title>
        <span class="text-h5">Eliminar elemento</span>
      </v-card-title>

      <v-divider></v-divider>

      <v-card-text>
        <v-container>
          ¿Seguro que desea eliminar el elemento?
          <v-list v-if="props.elementName">
            <v-list-item
              :key="props.item.id"
              :title="props.item[props.elementName]"
            >
              <template v-slot:prepend>
                <v-icon icon="mdi-menu-right"></v-icon>
              </template>
            </v-list-item>
          </v-list>
        </v-container>
      </v-card-text>

      <v-divider></v-divider>

      <v-card-actions class="d-flex justify-center">
        <v-btn color="red-darken-1" variant="text" @click="dialogState = false">
          Cerrar
        </v-btn>
        <v-btn color="red" variant="text" @click="submit"> Eliminar </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
