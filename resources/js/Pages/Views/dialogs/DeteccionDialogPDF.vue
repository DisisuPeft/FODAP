<script setup>
import { computed, onMounted, ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import axios from "axios";
import CustomSnackBar from "@/Components/CustomSnackBar.vue";
import Loading from "@/Components/Loading.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const snackSuccess = ref(false);
const timeout = ref(0);
const message = ref("");
const color = ref("");
const loading = ref(false);
const show = ref(false);

const props = defineProps({
    modelValue: Boolean,
    carreras: Array,
});
const emit = defineEmits(["update:modelValue", "form:deteccion"]);

const form = useForm({
    periodo: null,
    carrera: null,
    anio: null,
});
const periodos = [
    { id: 1, name: "ENERO-JUNIO" },
    { id: 2, name: "AGOSTO-DICIEMBRE" },
];
const fullYears = computed(() => {
    const maxYears = new Date().getFullYear() + 1;
    const minYears = maxYears - 7;
    const years = [];
    for (let i = maxYears; i >= minYears; i--) {
        years.push(i);
    }

    return years;
});

function submit() {
    emit("form:deteccion", form);
}

onMounted(() => {
    setTimeout(() => {
        snackSuccess.value = false;
    }, timeout.value);
});

const close = () => {
    show.value = false;
};
function closeDialog() {
    emit("update:modelValue", false);
}
</script>

<template>
    <v-dialog width="auto" v-model="props.modelValue" @click.self="closeDialog">
        <form @submit.prevent="submit">
            <v-card elevation="3" width="500" height="500">
                <v-card-title>Ingresar los datos para generar PDF</v-card-title>
                <v-card-text>
                    <label
                        for="carrera"
                        class="absolute text-md text-gray-500 dark:text-gray-400 bg-white left-1 pb-4 mb-5 ml-5"
                        >Carrera a la que va dirigida:
                    </label>
                    <div class="pt-5">
                        <v-select
                            v-model="form.carrera"
                            :items="props.carreras"
                            item-title="nameCarrera"
                            item-value="id"
                            variant="solo"
                        ></v-select>
                    </div>
                    <label
                        for="periodo"
                        class="absolute text-md text-gray-500 dark:text-gray-400 bg-white left-1 pb-4 mb-5 ml-5"
                        >Periodo:
                    </label>
                    <div class="pt-5">
                        <v-select
                            v-model="form.periodo"
                            :items="periodos"
                            item-title="name"
                            item-value="id"
                            variant="solo"
                        ></v-select>
                    </div>
                    <label
                        for="anio"
                        class="absolute text-md text-gray-500 dark:text-gray-400 bg-white left-1 pb-4 mb-5 ml-5"
                        >Año:
                    </label>
                    <div class="pt-5">
                        <v-select
                            v-model="form.anio"
                            :items="fullYears"
                            item-title="name"
                            item-value="id"
                            variant="solo"
                        ></v-select>
                    </div>
                </v-card-text>
                <v-card-actions>
                    <v-row justify="end">
                        <v-col cols="4">
                            <v-btn
                                color="error"
                                @click="emit('update:modelValue', false)"
                            >
                                Cancelar
                            </v-btn>
                        </v-col>
                        <v-col cols="4">
                            <v-btn
                                @click="submit"
                                color="success"
                                prepend-icon="mdi-file-pdf-box"
                            >
                                Generar
                            </v-btn>
                        </v-col>
                    </v-row>
                </v-card-actions>
            </v-card>
        </form>
    </v-dialog>

</template>

<style scoped></style>
