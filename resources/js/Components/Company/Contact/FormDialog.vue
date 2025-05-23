<script setup>
import { useForm } from "@inertiajs/vue3"
import { computed, watch, ref } from "vue"
import rules from "@/Utils/rules"

const props = defineProps(["show", "item", "type", "endPoint", "company"])
const emit = defineEmits(["closeDialog", "reloadItems"])

console.log(props)

const dialogState = computed({
  get: () => props.show,
  set: () => {
    emit("closeDialog")
  },
})

const form = ref(false)

const formData = useForm({
  name: "",
  surname: "",
  dni: "",
  email: "",
  phone: "",
  companies_id: props.company?.id,
})

watch(dialogState, (value) => {
  if (value) {
    if (props.type === "edit") {
      Object.assign(formData, props.item)
    } else if (props.type === "create") {
      formData.name = ""
      formData.surname = ""
      formData.dni = ""
      formData.email = ""
      formData.phone = ""
      formData.companies_id = props.company.id
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
          contacto</span
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
                  :rules="[
                    rules.ruleRequired,
                    (v) => rules.ruleMaxLength(v, 191),
                  ]"
                  v-model="formData.name"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Apellidos*"
                  :rules="[
                    rules.ruleRequired,
                    (v) => rules.ruleMaxLength(v, 191),
                  ]"
                  v-model="formData.surname"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="DNI*"
                  :rules="[
                    rules.ruleRequired,
                    (v) => rules.ruleMaxLength(v, 191),
                    (v) => rules.ruleDNI(v),
                  ]"
                  v-model="formData.dni"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Correo*"
                  :rules="[
                    rules.ruleEmail,
                    (v) => rules.ruleMaxLength(v, 191),
                    rules.ruleRequired,
                  ]"
                  v-model="formData.email"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Teléfono*"
                  :rules="[
                    (v) => rules.ruleMaxLength(v, 191),
                    (v) => rules.ruleTelephone(v),
                    rules.ruleRequired,
                  ]"
                  v-model="formData.phone"
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
