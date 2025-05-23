<script setup>
import { ref, computed } from "vue"
import axios from "axios"
import debounce from "lodash.debounce"

const props = defineProps([
  "modelValue",
  "itemTitle",
  "label",
  "rules",
  "endPoint",
  "hideDetails",
])
const emit = defineEmits(["update:modelValue"])

const loading = ref(false)
const items = ref([])

const selectedItem = computed({
  get: () => props.modelValue,
  set: (value) => emit("update:modelValue", value),
})

let waitingForData = false

const loadAutocompleteItems = (search) => {
  if (!loading.value) {
    loading.value = true
  }

  debounceLoadAutocompleteItems(search)
}

const debounceLoadAutocompleteItems = debounce((search) => {
  if (search) {
    if (waitingForData) return

    waitingForData = true

    axios
      .post(`${props.endPoint}/load-autocomplete-items`, { search: search })
      .then((response) => {
        items.value = response.data.autocompleteItems
        waitingForData = false
        loading.value = false
      })
  } else {
    items.value = []
    loading.value = false
  }
}, 500)
</script>

<template>
  <v-autocomplete
    clearable
    :item-title="props.itemTitle"
    :label="props.label"
    v-model="selectedItem"
    :loading="loading"
    @update:search="loadAutocompleteItems"
    return-object
    :rules="props.rules"
    :items="items"
    :hide-details="props.hideDetails"
  >
    <template v-if="$slots.append" v-slot:append>
      <slot name="append"></slot>
    </template>
    <template v-if="$slots.prepend" v-slot:prepend>
      <slot name="prepend"></slot>
    </template>
  </v-autocomplete>
</template>
