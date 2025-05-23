<script setup>
import { useForm } from "@inertiajs/vue3"
import { computed, ref, watch } from "vue"
import rules from "@/Utils/rules"

const props = defineProps(["show", "item", "type", "endPoint", "student"])
const emit = defineEmits(["closeDialog", "reloadItems"])

const dialogState = computed({
  get: () => props.show,
  set: () => {
    emit("closeDialog")
  },
})

const form = ref(false)

const formData = useForm({
  file: null,
  description: "",
  expiration_date: "",
  students_id: props.student?.id,
})

watch(dialogState, (value) => {
  if (value) {
    if (props.type === "edit") {
      Object.assign(formData, props.item)
    } else if (props.type === "create") {
      formData.file = null
      formData.description = ""
      formData.expiration_date = ""
      formData.students_id = props.student.id
    }
  } else {
    emit("reloadItems")
  }
})

const submit = () => {
  if (props.type === "edit") {
    formData.put(`${props.endPoint}/${props.item.id}`, {
      onSuccess: () => {
        dialogState.value = false
      },
    })
  } else if (props.type === "create") {
    formData.file = formData.file
    formData.post(props.endPoint, {
      onSuccess: () => {
        dialogState.value = false
      },
    })
  }
}
</script>

<template>
  <v-dialog scrollable v-model="dialogState" width="1024">
    <v-card>
      <v-card-title>
        <span class="text-h5"
          >{{
            props.type == "create"
              ? "Crear"
              : props.type == "edit"
              ? "Editar"
              : ""
          }}
          documento</span
        >
      </v-card-title>

      <v-divider></v-divider>

      <v-card-text>
        <v-container>
          <v-form v-model="form" @submit.prevent="submit">
            <v-row>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Fecha de expiración"
                  type="date"
                  v-model="formData.expiration_date"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Descripción"
                  :rules="[(v) => rules.ruleMaxLength(v, 191)]"
                  v-model="formData.description"
                ></v-text-field>
              </v-col>
              <v-col v-if="props.type != 'edit'" cols="12" sm="6">
                <v-file-input
                  label="Archivo"
                  v-model="formData.file"
                ></v-file-input>
              </v-col>
            </v-row>
          </v-form>
        </v-container>
      </v-card-text>

      <v-divider></v-divider>

      <v-card-actions class="d-flex justify-center">
        <v-btn color="red-darken-1" variant="text" @click="dialogState = false">
          Cerrar
        </v-btn>
        <v-btn
          color="blue-darken-1"
          :disabled="!form"
          variant="text"
          @click="submit"
        >
          Guardar
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
