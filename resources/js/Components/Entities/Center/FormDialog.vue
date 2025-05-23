<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3"
import { computed, watch, ref } from "vue"
import rules from "@/Utils/rules"
import { locationItems } from "@/Utils/arrays"

const props = defineProps(["show", "item", "type", "endPoint"])
const emit = defineEmits(["closeDialog", "reloadItems"])

const dialogState = computed({
  get: () => props.show,
  set: (value) => {
    emit("closeDialog", value)
  },
})

const type = ref()

const form = ref(false)

const formData = useForm({
  name: "",
  entities_id: null,
  island: null,
})

const item = ref()
const selectedUser = ref(null)

const entities = computed(() => {
  return usePage().props.entities
})

const users = computed(() => {
  return usePage().props.users?.filter((user) => {
    return !item.value.users.some((item) => item.id === user.id)
  })
})

watch(dialogState, (value) => {
  if (value) {
    router.reload({
      only: ["entities", "users", "flash", "errors"],
    })
    type.value = props.type
    if (props.type === "edit") {
      item.value = props.item
      Object.assign(formData, props.item)
    } else if (props.type === "create") {
      formData.name = ""
      formData.entities_id = null
      formData.island = null
    }
  } else {
    emit("reloadItems")
  }
})

watch(
  () => usePage().props.flash,
  (flash) => {
    if (flash.item && flash.itemType === "center") {
      item.value = flash.item
    }
  },
  { deep: true }
)

const submit = () => {
  if (props.type === "edit") {
    formData.put(`${props.endPoint}/${props.item.id}`, {
      only: ["entities", "users", "flash", "errors"],
    })
  } else if (props.type === "create") {
    formData.post(props.endPoint, {
      only: ["entities", "users", "flash", "errors"],
      onSuccess: () => {
        type.value = "edit"
      },
    })
  }
}

const addUser = () => {
  if (selectedUser.value) {
    router.post(
      `${props.endPoint}/${item.value.id}/add-user`,
      {
        users_id: selectedUser.value,
      },
      {
        only: ["entities", "users", "flash", "errors"],
        onSuccess: () => {
          selectedUser.value = null
        },
      }
    )
  }
}

const removeUser = (user) => {
  router.post(
    `${props.endPoint}/${item.value.id}/remove-user`,
    {
      users_id: user.id,
    },
    {
      only: ["entities", "users", "flash", "errors"],
    }
  )
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
          centro</span
        >
      </v-card-title>

      <v-divider></v-divider>

      <v-card-text>
        <v-container>
          <v-form v-model="form" @submit.prevent="submit">
            <v-row>
              <v-col cols="12">
                <v-text-field
                  label="Nombre*"
                  :rules="[
                    rules.ruleRequired,
                    (v) => rules.ruleMaxLength(v, 191),
                  ]"
                  v-model="formData.name"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-autocomplete
                  :items="entities"
                  item-title="name"
                  item-value="id"
                  label="Entidad*"
                  :rules="[rules.ruleRequired]"
                  v-model="formData.entities_id"
                ></v-autocomplete>
              </v-col>
              <v-col cols="12">
                <v-autocomplete
                  :items="locationItems"
                  item-title="name"
                  item-value="id"
                  label="Isla*"
                  :rules="[rules.ruleRequired]"
                  v-model="formData.island"
                ></v-autocomplete>
              </v-col>
            </v-row>
          </v-form>

          <div v-if="type === 'edit'">
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

            <v-divider :thickness="3" class="mt-2"></v-divider>

            <v-row class="align-center justify-center my-3">
              <v-col class="justify-center align-center text-center" cols="12">
                <span class="text-h5">Responsables</span>
              </v-col>
            </v-row>

            <v-row
              class="align-center justify-center my-3 mx-1 elevation-6 rounded pa-5'"
            >
              <v-col cols="12">
                <v-autocomplete
                  class="ma-3"
                  label="Añadir usuario"
                  v-model="selectedUser"
                  :items="users"
                  item-title="name"
                  item-value="id"
                  :rules="[]"
                  @update:modelValue="addUser"
                  hide-details
                >
                </v-autocomplete>
              </v-col>
            </v-row>

            <v-row
              v-if="item && item.users"
              v-for="user in item.users"
              class="align-center justify-center my-3 mx-1 elevation-6 rounded pa-2'"
            >
              <v-col cols="12" md="10" class="my-3">
                {{ user.name }}
              </v-col>
              <v-col cols="12" md="1" class="text-center">
                <v-btn
                  icon
                  density="compact"
                  variant="text"
                  @click="removeUser(user)"
                >
                  <v-icon>mdi-delete</v-icon>
                  <v-tooltip activator="parent">Eliminar</v-tooltip>
                </v-btn>
              </v-col>
            </v-row>
          </div>
        </v-container>
      </v-card-text>

      <v-divider></v-divider>

      <v-card-actions class="d-flex justify-center">
        <v-btn color="red-darken-1" variant="text" @click="dialogState = false">
          Cerrar
        </v-btn>
        <v-btn
          v-if="type == 'create'"
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
