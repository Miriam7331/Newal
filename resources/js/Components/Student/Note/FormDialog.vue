<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3"
import { computed, watch, ref } from "vue"
import rules from "@/Utils/rules"

const props = defineProps(["show", "item", "type", "endPoint", "student"])
const emit = defineEmits(["closeDialog", "reloadItems"])

const dialogState = computed({
  get: () => props.show,
  set: (value) => {
    emit("closeDialog", value)
  },
})

const form = ref(false)

const formData = useForm({
  note: "",
  users_id: usePage().props?.auth.user.id,
  students_id: props.student?.id,
})

const item = ref()

watch(dialogState, (value) => {
  if (value) {
    router.reload({
      only: ["flash", "errors"],
    })
    if (props.type === "edit") {
      item.value = props.item
      Object.assign(formData, props.item)
    } else if (props.type === "create") {
      formData.note = ""
      formData.users_id = usePage().props?.auth.user.id
      formData.students_id = props.student?.id
    }
  } else {
    emit("reloadItems")
  }
})

watch(
  () => usePage().props.flash,
  (flash) => {
    if (flash.item && flash.itemType === "note") {
      item.value = flash.item
    }
  },
  { deep: true }
)

const submit = () => {
  if (props.type === "edit") {
    formData.put(`${props.endPoint}/${props.item.id}`, {
      only: ["flash", "errors"],
      onSuccess: () => {
        dialogState.value = false
      },
    })
  } else if (props.type === "create") {
    formData.post(props.endPoint, {
      only: ["flash", "errors"],
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
          nota</span
        >
      </v-card-title>

      <v-divider></v-divider>

      <v-card-text>
        <v-container>
          <v-form v-model="form" @submit.prevent="submit">
            <v-row>
              <v-col cols="12">
                <v-text-field
                  label="Nota*"
                  :rules="[rules.ruleRequired]"
                  v-model="formData.note"
                ></v-text-field>
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
