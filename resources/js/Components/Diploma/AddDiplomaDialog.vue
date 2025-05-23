<script setup>
import { useForm } from "@inertiajs/vue3"
import { computed, ref, watch } from "vue"
import rules from "@/Utils/rules"

const props = defineProps(["show", "item", "endPoint", "student", "origin"])
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
  students_id: props.student?.id,
  formative_actions_id: props.item?.id,
})

watch(dialogState, (value) => {
  if (value) {
    formData.file = null
    formData.students_id = props.student.id
    formData.formative_actions_id = props.item.id
  } else {
    emit("reloadItems")
  }
})

const submit = () => {
  formData.file = formData.file ? formData.file : null
  formData
    .transform((data) => ({
      ...data,
      origin: props.origin ?? null,
    }))
    .post(
      props.endPoint + "/add-diploma",

      {
        onSuccess: () => {
          dialogState.value = false
        },
      }
    )
}
</script>

<template>
  <v-dialog scrollable v-model="dialogState" width="1024">
    <v-card>
      <v-card-title>
        <span class="text-h5">Añadir diploma</span>
      </v-card-title>

      <v-divider></v-divider>

      <v-card-text>
        <v-container>
          <v-form v-model="form" @submit.prevent="submit">
            <v-row>
              <v-col cols="12">
                <v-file-input
                  label="Diploma"
                  v-model="formData.file"
                  :rules="[rules.ruleRequired]"
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
