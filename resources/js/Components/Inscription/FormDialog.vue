<script setup>
import { useForm } from "@inertiajs/vue3"
import { computed, watch, ref } from "vue"
import rules from "@/Utils/rules"
import { locationItems } from "@/Utils/arrays"
import AutocompleteServer from "@/Components/AutocompleteServer.vue"

const props = defineProps(["show", "item", "type", "endPoint"])
const emit = defineEmits(["closeDialog", "reloadItems"])

const dialogState = computed({
  get: () => props.show,
  set: (value) => {
    emit("closeDialog", value)
  },
})

const form = ref(false)

const formData = useForm({
  name: "",
  email: "",
  phone: "",
  advertising: false,
  courses_id: null,
  web: "",
  island: "",
})

const selectedCourse = ref(null)

watch(dialogState, (value) => {
  if (value) {
    if (props.type === "edit") {
      Object.assign(formData, props.item)
      selectedCourse.value = props.item.course
    } else if (props.type === "create") {
      formData.name = ""
      formData.email = ""
      formData.phone = ""
      formData.advertising = false
      formData.courses_id = null
      formData.web = ""
      formData.island = ""

      selectedCourse.value = null
    }
  } else {
    emit("reloadItems")
  }
})

const submit = () => {
  formData.courses_id = selectedCourse.value.id

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
          inscripción</span
        >
      </v-card-title>

      <v-divider></v-divider>

      <v-card-text>
        <v-container>
          <v-form v-model="form" @submit.prevent="submit">
            <v-row>
              <v-col cols="12" sm="6">
                <autocomplete-server
                  :item-title="(item) => item.name + ' (' + item.code + ')'"
                  label="Curso*"
                  :rules="[rules.ruleRequired]"
                  v-model="selectedCourse"
                  end-point="/dashboard/courses"
                >
                </autocomplete-server>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Página*"
                  :rules="[
                    rules.ruleRequired,
                    (v) => rules.ruleMaxLength(v, 191),
                  ]"
                  v-model="formData.web"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-select
                  :items="locationItems"
                  label="Isla*"
                  :rules="[rules.ruleRequired]"
                  v-model="formData.island"
                ></v-select>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Nombre*"
                  :rules="[
                    rules.ruleRequired,
                    (v) => rules.ruleMaxLength(v, 191),
                  ]"
                  v-model="formData.name"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Email*"
                  :rules="[
                    rules.ruleRequired,
                    (v) => rules.ruleMaxLength(v, 191),
                    rules.ruleEmail,
                  ]"
                  v-model="formData.email"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Teléfono*"
                  :rules="[rules.ruleRequired, rules.ruleTelephone]"
                  v-model="formData.phone"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-checkbox
                  label="Publicidad"
                  v-model="formData.advertising"
                ></v-checkbox>
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
