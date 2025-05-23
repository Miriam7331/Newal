<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3"
import { computed, watch, ref } from "vue"
import rules from "@/Utils/rules"
import useDialogs from "@/Composables/useDialogs"
import DocumentFormDialog from "@/Components/Document/FormDialogCompany.vue"
import ContactFormDialog from "@/Components/Company/Contact/FormDialog.vue"
import DestroyPermanentDialog from "@/Components/DestroyPermanentDialog.vue"
import {
  locationItems,
  fuerteventuraItems,
  grancanariaItems,
  lanzaroteItems,
  tenerifeItems,
  lapalmaItems,
  elhierroItems,
  lagomeraItems,
} from "@/Utils/arrays"

const page = usePage()

const { formDialogType, showDestroyPermanentDialog, openDialog } = useDialogs()

const props = defineProps(["show", "item", "type", "endPoint"])
const emit = defineEmits(["closeDialog", "reloadItems"])

const dialogState = computed({
  get: () => props.show,
  set: (value) => {
    emit("closeDialog", value)
  },
})

const form = ref(false)
const item = ref({})

const formData = useForm({
  name: "",
  cif: "",
  email: "",
  phone: "",
  address: "",
  number: "",
  floor: "",
  door: "",
  island: "",
  municipality: "",
  cp: "",
})

const municipalities = ref([])

const islandMunicipalities = () => {
  const island = formData.island
  switch (island) {
    case "Fuerteventura":
      municipalities.value = fuerteventuraItems
      break
    case "Gran Canaria":
      municipalities.value = grancanariaItems
      break
    case "Lanzarote":
      municipalities.value = lanzaroteItems
      break
    case "Tenerife":
      municipalities.value = tenerifeItems
      break
    case "La Palma":
      municipalities.value = lapalmaItems
      break
    case "El Hierro":
      municipalities.value = elhierroItems
      break
    case "La Gomera":
      municipalities.value = lagomeraItems
      break
    case "Península":
      municipalities.value = []
      formData.municipality = ""
      break
    default:
      municipalities.value = []
      formData.municipality = ""
  }
}

watch(() => formData.island, islandMunicipalities)

watch(
  () => page.props.flash,
  (flash) => {
    if (flash.item) {
      if (flash.itemType === "company") {
        item.value = flash.item
      }
    }
  },
  { deep: true }
)

watch(dialogState, (value) => {
  if (value) {
    loadItems()
    if (props.type === "edit") {
      item.value = props.item
      Object.assign(formData, item.value)
    } else if (props.type === "create") {
      formData.name = ""
      formData.cif = ""
      formData.email = ""
      formData.phone = ""
      formData.address = ""
      formData.number = ""
      formData.floor = ""
      formData.door = ""
      formData.island = ""
      formData.municipality = ""
      formData.cp = ""
    }
  } else {
    emit("reloadItems")
  }
})

const submit = () => {
  if (props.type === "edit") {
    formData.put(`${props.endPoint}/${props.item.id}`, {
      only: ["flash", "errors"],
      onSuccess: () => {
        // item.value = flash.item
        item.value = page.props.flash.item
        dialogState.value = false
      },
    })
  } else if (props.type === "create") {
    formData.post(props.endPoint, {
      only: ["flash", "errors"],
      onSuccess: () => {
        //item.value = flash.item
        item.value = page.props.flash.item
        dialogState.value = false
        props.type = "edit"
      },
    })
  }
}

const loadItems = () => {
  router.reload({
    only: ["flash", "errors"],
  })
}

const itemDialog = ref()
const showDocumentFormDialog = ref(false)
const showContactFormDialog = ref(false)
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
const formRef = ref()
</script>

<template>
  <v-dialog scrollable v-model="dialogState" width="1024">
    <document-form-dialog
      :show="showDocumentFormDialog"
      :item="itemDialog"
      :type="formDialogType"
      :company="item"
      endPoint="/dashboard/companyDocuments"
      @closeDialog="showDocumentFormDialog = false"
      @reloadItems="loadItems"
    />
    <contact-form-dialog
      :show="showContactFormDialog"
      :item="itemDialog"
      :type="formDialogType"
      :company="item"
      endPoint="/dashboard/companyContacts"
      @closeDialog="showContactFormDialog = false"
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
          empresa</span
        >
      </v-card-title>

      <v-divider></v-divider>

      <v-card-text>
        <v-container>
          <v-form v-model="form" ref="formRef" @submit.prevent="submit">
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
                  label="CIF*"
                  :rules="[
                    rules.ruleRequired,
                    (v) => rules.ruleMaxLength(v, 191),
                    (v) => rules.ruleCIF(v),
                  ]"
                  v-model="formData.cif"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Correo"
                  :rules="[rules.ruleEmail, (v) => rules.ruleMaxLength(v, 191)]"
                  v-model="formData.email"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Teléfono"
                  :rules="[
                    (v) => rules.ruleMaxLength(v, 191),
                    (v) => rules.ruleTelephone(v),
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
                  label="Número"
                  :rules="[
                    //(v) => rules.ruleMaxLength(v, 5)
                  ]"
                  v-model="formData.number"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Piso"
                  :rules="[
                    //(v) => rules.ruleMaxLength(v, 5)
                  ]"
                  v-model="formData.floor"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Puerta"
                  :rules="[
                    //(v) => rules.ruleMaxLength(v, 3)
                  ]"
                  v-model="formData.door"
                ></v-text-field>
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
                <v-select
                  label="Municipio*"
                  v-model="formData.municipality"
                  :rules="
                    formData.island !== 'Península' ? [rules.ruleRequired] : []
                  "
                  :items="municipalities"
                  :disabled="formData.island === 'Península'"
                  @update:modelValue="() => formRef?.value?.validate?.()"
                ></v-select>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  required
                  label="Código Postal*"
                  :rules="[
                    rules.ruleRequired,
                    (v) => rules.ruleMaxLength(v, 191),
                  ]"
                  v-model="formData.cp"
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
              <v-col cols="12" sm="6">
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
                  v-if="item.documents && item.documents.length > 0"
                  v-for="document in item.documents"
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
                                '/dashboard/companyDocuments'
                              openDialog('destroyPermanent')
                            }
                          "
                        ></v-btn>
                      </div>
                    </v-col>
                  </v-row>
                </div>
              </v-col>
              <v-col cols="12" sm="6">
                <div
                  class="d-flex flex-column align-center justify-center py-3"
                >
                  <span class="text-h5">Contactos</span>
                  <v-btn
                    icon="mdi-plus"
                    class="my-3"
                    @click="
                      () => {
                        formDialogType = 'create'
                        showContactFormDialog = true
                      }
                    "
                  ></v-btn>
                </div>

                <div
                  v-if="item.contacts && item.contacts.length > 0"
                  v-for="contact in item.contacts"
                  :key="contact.id"
                  class="elevation-6 rounded"
                >
                  <v-row class="ma-0 mb-3 px-2 pt-2">
                    <v-col cols="12" class="d-flex align-center">
                      <v-row>
                        <v-col class="pa-0" cols="12">
                          <v-chip class="w-100 justify-center">
                            {{ contact.name }} <span>&nbsp;</span>
                            {{ contact.surname }}
                          </v-chip>
                        </v-col>
                      </v-row>
                    </v-col>

                    <v-col>
                      <div
                        class="d-flex flex-column h-100 align-center text-center"
                      >
                        <div class="w-100">
                          {{ contact.email }}
                        </div>
                        <div class="w-100">
                          {{ contact.phone }}
                        </div>
                      </div>
                    </v-col>

                    <v-col cols="12">
                      <div class="d-flex justify-center h-100 align-center">
                        <v-btn
                          class="mr-2"
                          density="compact"
                          variant="text"
                          icon="mdi-pencil"
                          @click="
                            () => {
                              itemDialog = contact
                              formDialogType = 'edit'
                              showContactFormDialog = true
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
                              itemDialog = contact
                              destroyDialogEndpoint =
                                '/dashboard/companyContacts'
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
