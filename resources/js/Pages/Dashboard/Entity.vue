<script setup>
import CenterTable from "@/Components/Entities/CenterTable.vue"
import EntityTable from "@/Components/Entities/EntityTable.vue"
</script>

<template>
  <v-row>
    <v-col cols="12" md="6" class="pr-md-2">
      <center-table />
    </v-col>
    <v-col cols="12" md="6" class="pl-md-2">
      <entity-table />
    </v-col>
  </v-row>
</template>
