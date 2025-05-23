<script setup>
import { router, usePage, useForm } from "@inertiajs/vue3"
import { computed, ref, watch } from "vue"
import rules from "@/Utils/rules"

const props = defineProps(["show", "item", "type", "endPoint"])
const emit = defineEmits(["closeDialog", "reloadItems"])

const dialogState = computed({
  get: () => props.show,
  set: () => {
    emit("closeDialog")
  },
})

const form = ref(false)

const formData = useForm({
  name: "",
  password: "",
  email: "",
  admin: "",
})

const item = ref()

watch(dialogState, (value) => {
  if (value) {
    router.reload({
      only: ["flash", "errors"],
    })
    if (props.type === "edit") {
      item.value = props.item
      Object.assign(formData, item.value)
    } else if (props.type === "create") {
      formData.name = ""
      formData.password = ""
      formData.email = ""
      formData.admin = false
    }
  } else {
    emit("reloadItems")
  }
})

watch(
  () => usePage().props.flash,
  (flash) => {
    if (flash.item && flash.itemType === "user") {
      item.value = flash.item
    }
  },
  { deep: true }
)

const submit = () => {
  if (props.type === "edit") {
    formData.put(`${props.endPoint}/${props.item.id}`, {
      only: ["flash", "errors"],
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
          usuario</span
        >
      </v-card-title>

      <v-divider></v-divider>

      <v-card-text>
        <v-container>
          <v-form v-model="form" @submit.prevent="submit">
            <v-row>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Nombre*"
                  v-model="formData.name"
                  :rules="[
                    rules.ruleRequired,
                    (v) => rules.ruleMaxLength(v, 191),
                  ]"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  label="Email*"
                  v-model="formData.email"
                  :rules="[
                    rules.ruleRequired,
                    rules.ruleEmail,
                    (v) => rules.ruleMaxLength(v, 45),
                  ]"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  label="Contraseña"
                  v-model="formData.password"
                  type="password"
                  :rules="[(v) => rules.ruleMaxLength(v, 191)]"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="4">
                <v-checkbox label="Admin" v-model="formData.admin"></v-checkbox>
              </v-col>
            </v-row>
          </v-form>
          <div v-if="props.type === 'edit'">
            <div class="d-flex justify-center">
              <v-btn
                color="blue-darken-1"
                :disabled="!form"
                variant="text"
                @click="submit"
              >
                Guardar
              </v-btn>
            </div>
          </div>
        </v-container>
      </v-card-text>

      <v-divider></v-divider>

      <v-card-actions class="d-flex justify-center">
        <v-btn color="red-darken-1" variant="text" @click="dialogState = false">
          Cerrar
        </v-btn>
        <v-btn
          v-if="props.type == 'create'"
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
