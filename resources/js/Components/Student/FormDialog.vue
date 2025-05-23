<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3"
import { computed, watch, ref } from "vue"
import rules from "@/Utils/rules"
import useDialogs from "@/Composables/useDialogs"
import DocumentFormDialog from "@/Components/Document/FormDialog.vue"
import NoteFormDialog from "@/Components/Student/Note/FormDialog.vue"
import DestroyPermanentDialog from "@/Components/DestroyPermanentDialog.vue"
import { locationItems, genderItems, provinceItems } from "@/Utils/arrays"
import RelatedInscriptionsTable from "./RelatedInscriptionsTable.vue"

const { formDialogType, showDestroyPermanentDialog, openDialog } = useDialogs()

const props = defineProps([
  "show",
  "item",
  "type",
  "endPoint",
  "fromInscription",
])
const emit = defineEmits(["closeDialog", "reloadItems"])

const dialogState = computed({
  get: () => props.show,
  set: (value) => {
    emit("closeDialog", value)
  },
})

const type = ref()

const form = ref(false)
const item = ref()

const formData = useForm({
  name: "",
  email: "",
  level: "",
  dni: "",
  ssn: "",
  phone: "",
  address: "",
  cp: "",
  city: "",
  province: "",
  island: "",
  gender: "",
  birthdate: "",
  disability: 0,
  consent: 0,
})

watch(
  () => usePage().props.flash,
  (flash) => {
    if (flash.item) {
      if (flash.itemType === "student") {
        item.value = flash.item
        Object.assign(formData, item.value)
      }
    }
  },
  { deep: true }
)

watch(dialogState, (value) => {
  if (value) {
    loadItems()
    type.value = props.type
    if (type.value === "edit") {
      item.value = props.item
      Object.assign(formData, item.value)
    } else if (type.value === "create") {
      formData.name = ""
      formData.email = ""
      formData.level = ""
      formData.dni = ""
      formData.ssn = ""
      formData.phone = ""
      formData.address = ""
      formData.cp = ""
      formData.city = ""
      formData.province = ""
      formData.island = ""
      formData.gender = ""
      formData.birthdate = ""
      formData.disability = 0
      formData.consent = 0

      if (props.item && props.fromInscription) {
        item.value = props.item
        Object.assign(formData, item.value)
      }
    }
  } else {
    emit("reloadItems")
  }
})

const submit = () => {
  if (type.value === "edit") {
    formData.put(`${props.endPoint}/${props.item.id}`, {
      only: ["formativeActions", "flash", "errors"],
    })
  } else if (type.value === "create") {
    formData.post(props.endPoint, {
      only: ["formativeActions", "flash", "errors"],
      onSuccess: () => {
        type.value = "edit"
      },
    })
  }
}

const loadItems = () => {
  router.reload({
    only: ["formativeActions", "flash", "errors"],
  })
}

const itemDialog = ref()
const showDocumentFormDialog = ref(false)
const showNoteFormDialog = ref(false)
const destroyDialogEndpoint = ref("")

const dateExpired = (date) => {
  const today = new Date().toISOString().slice(0, 10)
  if (date < today) {
    return true
  } else {
    return false
  }
}

const formatDateTime = (date) => {
  return new Date(date).toLocaleDateString()
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
      :student="item"
      endPoint="/dashboard/documents"
      @closeDialog="showDocumentFormDialog = false"
      @reloadItems="loadItems"
    />
    <note-form-dialog
      :show="showNoteFormDialog"
      :item="itemDialog"
      :type="formDialogType"
      :student="item"
      endPoint="/dashboard/notes"
      @closeDialog="showNoteFormDialog = false"
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
            type == "create" ? "Crear" : type == "edit" ? "Editar" : ""
          }}
          alumno</span
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
                  label="Correo*"
                  :rules="[
                    rules.ruleRequired,
                    rules.ruleEmail,
                    (v) => rules.ruleMaxLength(v, 191),
                  ]"
                  v-model="formData.email"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Nivel"
                  :rules="[(v) => rules.ruleMaxLength(v, 191)]"
                  v-model="formData.level"
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
                  label="SSN"
                  :rules="[(v) => rules.ruleMaxLength(v, 191)]"
                  v-model="formData.ssn"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-select
                  label="Género*"
                  v-model="formData.gender"
                  :rules="[
                    (v) => rules.ruleMaxLength(v, 45),
                    rules.ruleRequired,
                  ]"
                  :items="genderItems"
                  required
                ></v-select>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Teléfono*"
                  :rules="[
                    rules.ruleRequired,
                    (v) => rules.ruleMaxLength(v, 191),
                  ]"
                  v-model="formData.phone"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Dirección"
                  :rules="[(v) => rules.ruleMaxLength(v, 191)]"
                  v-model="formData.address"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Código Postal"
                  :rules="[(v) => rules.ruleMaxLength(v, 191)]"
                  v-model="formData.cp"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Localidad"
                  :rules="[(v) => rules.ruleMaxLength(v, 191)]"
                  v-model="formData.city"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-select
                  label="Provincia"
                  v-model="formData.province"
                  :rules="[(v) => rules.ruleMaxLength(v, 45)]"
                  :items="provinceItems"
                  required
                ></v-select>
              </v-col>
              <v-col cols="12" sm="6">
                <v-select
                  label="Isla*"
                  v-model="formData.island"
                  :rules="[
                    (v) => rules.ruleMaxLength(v, 45),
                    rules.ruleRequired,
                  ]"
                  :items="locationItems"
                  required
                ></v-select>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  type="date"
                  label="Fecha de nacimiento"
                  v-model="formData.birthdate"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Discapacidad"
                  v-model="formData.disability"
                  type="number"
                  :rules="[
                    (v) => rules.ruleGreaterThan(v, -1),
                    (v) => rules.ruleLessThan(v, 101),
                  ]"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-checkbox
                  label="Consentimiento"
                  v-model="formData.consent"
                ></v-checkbox>
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

            <v-divider
              :thickness="3"
              class="mt-2"
              v-if="
                item.matchingInscriptions &&
                item.matchingInscriptions.length > 0
              "
            ></v-divider>
            <v-row
              v-if="
                item.matchingInscriptions &&
                item.matchingInscriptions.length > 0
              "
            >
              <v-col cols="12">
                <div
                  class="d-flex flex-column align-center justify-center py-3"
                >
                  <span class="text-h5">Inscripciones relacionadas</span>
                  <related-inscriptions-table
                    :items="item.matchingInscriptions"
                  ></related-inscriptions-table>
                </div>
              </v-col>
            </v-row>
            <v-divider :thickness="3" class="mt-2"></v-divider>
            <v-row>
              <v-col cols="12" md="6">
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
                  v-for="document in item.documents"
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
                              destroyDialogEndpoint = '/dashboard/documents'
                              openDialog('destroyPermanent')
                            }
                          "
                        ></v-btn>
                      </div>
                    </v-col>
                  </v-row>
                </div>
              </v-col>
              <v-col cols="12" md="6">
                <div
                  class="d-flex flex-column align-center justify-center py-3"
                >
                  <span class="text-h5">Notas</span>
                  <v-btn
                    icon="mdi-plus"
                    class="my-3"
                    @click="
                      () => {
                        formDialogType = 'create'
                        showNoteFormDialog = true
                      }
                    "
                  ></v-btn>
                </div>

                <div v-for="note in item.notes" class="elevation-6 rounded">
                  <v-row class="ma-0 mb-3 px-2 pt-2">
                    <v-col cols="12" class="d-flex align-center">
                      <v-row>
                        <v-col class="pa-0 pr-1" cols="6">
                          <v-chip class="mr-2 w-100 justify-center">
                            <v-icon
                              start
                              icon="mdi-account-circle-outline"
                            ></v-icon>
                            {{ note.user.name }}
                          </v-chip>
                        </v-col>
                        <v-col class="pa-0" cols="6">
                          <v-chip class="mr-1 w-100 justify-center">
                            <v-icon start icon="mdi-calendar"></v-icon>
                            {{ formatDateTime(note.updated_at) }}
                          </v-chip>
                        </v-col>
                      </v-row>
                    </v-col>

                    <v-col>
                      <div class="d-flex flex-column h-100 align-center">
                        <div class="w-100 text-center">{{ note.note }}</div>
                      </div>
                    </v-col>

                    <v-col
                      v-if="usePage().props?.auth.user.id == note.user.id"
                      cols="12"
                    >
                      <div class="d-flex justify-center h-100 align-center">
                        <v-btn
                          class="mr-2"
                          density="compact"
                          variant="text"
                          icon="mdi-pencil"
                          @click="
                            () => {
                              itemDialog = note
                              formDialogType = 'edit'
                              showNoteFormDialog = true
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
                              itemDialog = note
                              destroyDialogEndpoint = '/dashboard/notes'
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
