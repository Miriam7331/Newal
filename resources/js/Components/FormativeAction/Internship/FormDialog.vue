<script setup>
import { computed, watch, ref } from "vue"
import { router, useForm, usePage } from "@inertiajs/vue3"
import rules from "@/Utils/rules"
import DocumentFormDialog from "@/Components/Document/FormDialogInternship.vue"
import DestroyPermanentDialog from "@/Components/DestroyPermanentDialog.vue"
import useDialogs from "@/Composables/useDialogs"

const props = defineProps([
  "show",
  "item",
  "type",
  "endPoint",
  "internship",
  "formativeAction",
])
const emit = defineEmits(["closeDialog", "reloadItems"])

const page = usePage()

const dialogState = computed({
  get: () => props.show,
  set: (value) => {
    emit("closeDialog", value)
  },
})

const type = ref()
const form = ref(false)

const formData = useForm({
  formative_actions_has_students_id: null,
  companies_id: null,
  start_date: "",
  end_date: "",
  schedule: "",
})

const item2 = ref()

const companies = computed(() => {
  return page.props.companies ?? []
})

const students = computed(() => {
  return props.formativeAction.students ?? []
})

watch(
  () => usePage().props.flash,
  (flash) => {
    if (flash.item) {
      if (flash.itemType === "internship") {
        item2.value = flash.item
        Object.assign(formData, item2.value)
      }
    }
  },
  { deep: true }
)

watch(dialogState, (value) => {
  if (value) {
    router.reload({
      only: ["companies", "flash", "errors"],
    })
    type.value = props.type

    if (props.type === "edit") {
      item2.value = props.item
      Object.assign(formData, props.item)
    } else if (props.type === "create") {
      formData.formative_actions_has_students_id = null
      formData.companies_id = null
      formData.start_date = ""
      formData.end_date = ""
      formData.schedule = ""
    }
  } else {
    emit("reloadItems")
  }
})

const submit = () => {
  if (props.type === "edit") {
    formData.put(`${props.endPoint}/${props.item.id}`, {
      only: ["flash", "errors", "companies"],
    })
  } else if (props.type === "create") {
    formData.post(props.endPoint, {
      only: ["flash", "errors", "companies"],
      onSuccess: () => {
        type.value = "edit"
      },
    })
  }
}

const loadItems = () => {
  router.reload({
    only: ["companies", "flash", "errors"],
  })
}

const { formDialogType, showDestroyPermanentDialog, openDialog } = useDialogs()
const itemDialog = ref()
const showDocumentFormDialog = ref(false)
const destroyDialogEndpoint = ref("")

const dateExpired = (date) => {
  const today = new Date().toISOString().slice(0, 10)
  if (date < today) {
    return true
  } else {
    return false
  }
}

const downloadDocument = (document) => {
  window.open(`documents/${document.id}/download`, "_blank")
}
</script>

<template>
  <v-dialog scrollable v-model="dialogState" width="1024">
    <document-form-dialog
      :show="showDocumentFormDialog"
      :item="itemDialog"
      :type="formDialogType"
      :internship="item2"
      endPoint="/dashboard/internshipDocuments"
      @closeDialog="showDocumentFormDialog = false"
      @reloadItems="loadItems"
    />
    <destroy-permanent-dialog
      :show="showDestroyPermanentDialog"
      @closeDialog="showDestroyPermanentDialog = false"
      @reloadItems="loadItems"
      :item="itemDialog"
      :endPoint="destroyDialogEndpoint"
    />
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
          práctica</span
        >
      </v-card-title>

      <v-divider></v-divider>

      <v-card-text>
        <v-container>
          <v-form v-model="form" @submit.prevent="submit">
            <v-row>
              <v-col cols="12" sm="6">
                <v-autocomplete
                  :items="students"
                  item-title="name"
                  item-value="pivot.id"
                  label="Estudiante*"
                  :rules="[rules.ruleRequired]"
                  v-model="formData.formative_actions_has_students_id"
                ></v-autocomplete>
              </v-col>
              <v-col cols="12" sm="6">
                <v-autocomplete
                  :items="companies"
                  item-title="name"
                  item-value="id"
                  label="Empresa*"
                  :rules="[rules.ruleRequired]"
                  v-model="formData.companies_id"
                ></v-autocomplete>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  label="Fecha de inicio*"
                  v-model="formData.start_date"
                  :rules="[rules.ruleRequired]"
                  type="date"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Fecha de finalización*"
                  v-model="formData.end_date"
                  :rules="[rules.ruleRequired]"
                  type="date"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Horario*"
                  v-model="formData.schedule"
                  :rules="[(v) => rules.ruleTimeRange(v), rules.ruleRequired]"
                ></v-text-field>
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
                >Guardar</v-btn
              >
            </div>
            <v-divider></v-divider>
            <v-row>
              <v-col cols="12">
                <div
                  class="d-flex flex-column align-center justify-center py-3"
                >
                  <span class="text-h5">Documentos</span>
                  <v-btn
                    icon="mdi-plus"
                    class="my-3"
                    @click="
                      () => {
                        formDialogType = 'create'
                        showDocumentFormDialog = true
                      }
                    "
                  ></v-btn>
                </div>

                <div
                  v-if="item2?.documents && item2?.documents.length > 0"
                  v-for="document in item2?.documents"
                  :key="document.id"
                  class="elevation-6 rounded"
                >
                  <v-row class="ma-0 mb-3 px-2 pt-2">
                    <v-col cols="12" class="d-flex align-center">
                      <v-row>
                        <v-col class="pa-0" cols="12">
                          <v-chip
                            class="w-100 justify-center"
                            :color="
                              dateExpired(document.expiration_date)
                                ? 'red'
                                : 'default'
                            "
                          >
                            <v-icon start icon="mdi-calendar"></v-icon>
                            {{ document.expiration_date ?? "Sin expiración" }}
                          </v-chip>
                        </v-col>
                      </v-row>
                    </v-col>

                    <v-col>
                      <div
                        class="d-flex flex-column h-100 align-center text-center"
                      >
                        <div class="w-100">
                          {{ document.original_name }}
                        </div>
                        <v-divider
                          v-if="document.description"
                          class="w-100 mb-1"
                        ></v-divider>
                        <div class="w-100">
                          {{ document.description }}
                        </div>
                      </div>
                    </v-col>

                    <v-col cols="12">
                      <div class="d-flex justify-center h-100 align-center">
                        <v-btn
                          class="mr-2"
                          density="compact"
                          variant="text"
                          icon="mdi-download"
                          @click="downloadDocument(document)"
                        >
                        </v-btn>
                        <v-btn
                          class="mr-2"
                          density="compact"
                          variant="text"
                          icon="mdi-pencil"
                          @click="
                            () => {
                              itemDialog = document
                              formDialogType = 'edit'
                              showDocumentFormDialog = true
                            }
                          "
                        >
                        </v-btn>
                        <v-btn
                          icon="mdi-delete"
                          density="compact"
                          variant="text"
                          @click="
                            () => {
                              itemDialog = document
                              destroyDialogEndpoint =
                                '/dashboard/internshipDocuments'
                              openDialog('destroyPermanent')
                            }
                          "
                        ></v-btn>
                      </div>
                    </v-col>
                  </v-row>
                </div>
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
