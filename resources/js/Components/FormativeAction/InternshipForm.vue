<script setup>
import { router, usePage } from "@inertiajs/vue3"
import { computed, watch, ref } from "vue"

import useDialogs from "@/Composables/useDialogs"

import InternshipFormDialog from "@/Components/FormativeAction/Internship/FormDialog.vue"
import DestroyPermanentDialog from "@/Components/DestroyPermanentDialog.vue"

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

const item = ref({})

const itemFormativeAction = ref(props.item)

watch(
  () => page.props.flash,
  (flash) => {
    if (flash.item) {
      if (flash.itemType === "company") {
        item.value = flash.item
      }

      if (flash.itemType === "formativeAction") {
        itemFormativeAction.value = flash.item
      }

      if (flash.itemType === "internship") {
        itemFormativeAction.value.formative_action_has_student?.forEach(
          (formActStud) => {
            formActStud.internship?.forEach((internship, i) => {
              if (internship.id === flash.item.id) {
                formActStud.internship[i].documents = flash.item.documents
              }
            })
          }
        )
      }
    }
  },
  { deep: true }
)

const submit = () => {
  if (props.type === "edit") {
    formData.put(`${props.endPoint}/${props.item.id}`, {
      only: ["flash", "errors"],
      onSuccess: () => {
        item.value = flash.item
        dialogState.value = false
      },
    })
  } else if (props.type === "create") {
    formData.post(props.endPoint, {
      only: ["flash", "errors"],
      onSuccess: () => {
        item.value = flash.item
        dialogState.value = false
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

const showInternshipFormDialog = ref(false)
const destroyDialogEndpoint = ref("")
</script>

<template>
  <InternshipFormDialog
    :show="showInternshipFormDialog"
    :item="itemDialog"
    :type="formDialogType"
    :formative-action="props.item"
    endPoint="/dashboard/internships"
    @closeDialog="showInternshipFormDialog = false"
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
    <v-card-text>
      <v-container>
        <v-row>
          <v-col cols="12">
            <div class="d-flex flex-column align-center justify-center py-3">
              <span class="text-h5">Prácticas</span>
              <v-btn
                icon="mdi-plus"
                class="my-3"
                @click="
                  () => {
                    formDialogType = 'create'
                    showInternshipFormDialog = true
                  }
                "
              ></v-btn>
            </div>

            <div
              v-for="students in itemFormativeAction.formative_action_has_student"
              :key="students.id"
            >
              <div
                v-if="students.internship && students.internship.length > 0"
                v-for="internships in students.internship"
                :key="internships.id"
                class="elevation-6 rounded"
              >
                <v-row class="ma-0 mb-3 px-1 pt-2">
                  <v-col cols="12" class="d-flex align-center pa-1">
                    <v-row>
                      <v-col class="px-1 py-1" cols="12" sm="6">
                        <v-chip class="w-100 justify-center">
                          <div>
                            <strong>Estudiante: </strong>
                            {{ students.student.name }}
                          </div>
                        </v-chip>
                      </v-col>
                      <v-col class="px-1 py-1" cols="12" sm="6">
                        <v-chip class="w-100 justify-center">
                          <div>
                            <strong> Empresa: </strong>
                            {{ internships.company.name }}
                          </div>
                        </v-chip>
                      </v-col>
                    </v-row>
                  </v-col>

                  <v-col>
                    <div
                      class="d-flex flex-column h-100 align-center text-center"
                    >
                      <div class="text-center">
                        <div>
                          <strong>Fecha de inicio:</strong>
                          {{ internships.start_date }}
                        </div>
                        <div>
                          <strong>Fecha de fin:</strong>
                          {{ internships.end_date }}
                        </div>
                        <div>
                          <strong>Horario:</strong>
                          {{ internships.schedule }}
                        </div>
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
                            itemDialog = internships
                            formDialogType = 'edit'
                            showInternshipFormDialog = true
                          }
                        "
                      >
                      </v-btn>
                      <v-btn
                        class="mr-2"
                        icon="mdi-delete"
                        density="compact"
                        variant="text"
                        @click="
                          () => {
                            itemDialog = internships
                            destroyDialogEndpoint = '/dashboard/internships'
                            openDialog('destroyPermanent')
                          }
                        "
                      ></v-btn>

                      <v-badge
                        :content="internships.documents?.length || 0"
                        color="primary"
                        overlap
                      >
                        <v-btn
                          icon="mdi-file-document"
                          density="compact"
                          variant="text"
                          :disabled="true"
                          style="opacity: 1"
                        ></v-btn>
                      </v-badge>
                    </div>
                  </v-col>
                </v-row>
              </div>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </v-card-text>

    <v-divider></v-divider>
  </v-card>
</template>
