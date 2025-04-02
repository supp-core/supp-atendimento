<template>
    <div class="date-range-container">
      <label class="date-label">Intervalo de Datas</label>
      <div class="date-inputs">
        <v-menu
          v-model="startDateMenu"
          :close-on-content-click="false"
          transition="scale-transition"
          offset-y
          min-width="auto"
        >
          <template v-slot:activator="{ props }">
            <v-text-field
              v-model="formattedStartDate"
              placeholder="Data inicial"
              prepend-icon="mdi-calendar"
              readonly
              v-bind="props"
              density="compact"
              variant="outlined"
              hide-details
            ></v-text-field>
          </template>
          <v-date-picker
            v-model="startDate"
            @input="startDateMenu = false"
            locale="pt-BR"
          ></v-date-picker>
        </v-menu>
  
        <span class="date-separator">até</span>
  
        <v-menu
          v-model="endDateMenu"
          :close-on-content-click="false"
          transition="scale-transition"
          offset-y
          min-width="auto"
        >
          <template v-slot:activator="{ props }">
            <v-text-field
              v-model="formattedEndDate"
              placeholder="Data final"
              prepend-icon="mdi-calendar"
              readonly
              v-bind="props"
              density="compact"
              variant="outlined"
              hide-details
            ></v-text-field>
          </template>
          <v-date-picker
            v-model="endDate"
            @input="endDateMenu = false"
            locale="pt-BR"
          ></v-date-picker>
        </v-menu>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, computed, watch } from 'vue';
  import { format, parseISO } from 'date-fns';
  import { ptBR } from 'date-fns/locale';
  
  export default {
    name: 'DateRangeSelector',
    props: {
      modelValue: {
        type: Object,
        default: () => ({ startDate: null, endDate: null })
      }
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {
      const startDate = ref(props.modelValue?.startDate || null);
      const endDate = ref(props.modelValue?.endDate || null);
      const startDateMenu = ref(false);
      const endDateMenu = ref(false);
  
      const formattedStartDate = computed(() => {
        return startDate.value ? format(parseISO(startDate.value), 'dd/MM/yyyy', { locale: ptBR }) : '';
      });
  
      const formattedEndDate = computed(() => {
        return endDate.value ? format(parseISO(endDate.value), 'dd/MM/yyyy', { locale: ptBR }) : '';
      });
  
      watch([startDate, endDate], () => {
        emit('update:modelValue', {
          startDate: startDate.value,
          endDate: endDate.value
        });
      });
  
      return {
        startDate,
        endDate,
        startDateMenu,
        endDateMenu,
        formattedStartDate,
        formattedEndDate
      };
    }
  }
  </script>
  
  <style scoped>
  .date-range-container {
    width: 100%;
  }
  
  .date-label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    color: rgba(0, 0, 0, 0.6);
  }
  
  .date-inputs {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  
  .date-separator {
    margin: 0 4px;
    color: rgba(0, 0, 0, 0.6);
  }
  </style>