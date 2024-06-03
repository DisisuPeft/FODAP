<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import NavLink from "@/Components/NavLink.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { ref, computed } from "vue";

const search = ref();
const fd = ref(null);
const ap = ref(null);
const periodo = ref(null);
const anio = ref();
const cursos_fd = ref();
const props = defineProps({
    detecciones: {
        type: Array,
    },
    auth: Object,
});
const tipos_cursos = [
    { id: 1, text: "FORMACION DOCENTE" },
    { id: 2, text: "ACTUALIZACION PROFESIONAL" },
];
const fullYears = computed(() => {
    const maxYears = new Date().getFullYear() + 1;
    const minYears = maxYears - 3;
    const years = [];
    for (let i = maxYears; i >= minYears; i--) {
        years.push(i);
    }

    return years;
});
const period = [
    { id: 1, text: "ENERO-JUNIO" },
    { id: 2, text: "AGOSTO-DICIEMBRE" },
];
const filter = computed(() => {
    let formacion = fd.value;
    let p = periodo.value;
    let a = anio.value;

    let cursosFiltrados = [...props.detecciones];

    if (formacion) {
        cursosFiltrados = cursosFiltrados.filter((c) => {
            // console.log(c.tipo_FDoAP, formacion);
            return c.tipo_FDoAP === formacion;
        });
    }
    if (p) {
        cursosFiltrados = cursosFiltrados.filter((c) => {
            return c.periodo === p;
        });
    }
    if (a) {
        cursosFiltrados = cursosFiltrados.filter((c) => {
            const parse_anio = new Date(c.fecha_I).getFullYear();
            return parse_anio === a;
        });
    }
    return cursosFiltrados;
});
// console.log(fd);
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Registros de cursos
            </h2>
        </template>

        <div class="mt-2 mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 mt-2 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex justify-center mt-5 mb-5 ma-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
                        <div class="flex justify-center">
                            <div class="grid grid-rows-1">
                                <div class="flex justify-start">
                                    <label class="text-sm">
                                        Filtrar por tipo de curso (Formación
                                        docente o Actualización profesional)
                                    </label>
                                </div>
                                <div class="flex justify-center">
                                    <select v-model="fd">
                                        <option></option>
                                        <option
                                            v-for="cursos in tipos_cursos"
                                            :value="cursos.id"
                                            :key="cursos.id"
                                        >
                                            {{ cursos.text }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-center">
                            <div class="grid grid-rows-1">
                                <div class="flex justify-start">
                                    <label class="text-sm">
                                        Filtrar por periodo
                                    </label>
                                </div>
                                <div class="flex justify-center">
                                    <select v-model="periodo">
                                        <option></option>
                                        <option
                                            v-for="pp in period"
                                            :value="pp.id"
                                            :key="pp.id"
                                        >
                                            {{ pp.text }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-center">
                            <div class="grid grid-rows-1">
                                <div class="flex justify-start">
                                    <label class="text-sm">
                                        Filtrar por año
                                    </label>
                                </div>
                                <div class="flex justify-center">
                                    <select v-model="anio">
                                        <option></option>
                                        <option
                                            v-for="an in fullYears"
                                            :value="an"
                                            :key="an"
                                        >
                                            {{ an }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <template v-if="props.detecciones.length !== 0">
                    <v-data-iterator
                        :items="filter"
                        item-value="nombreCurso"
                        :search="search"
                    >
                        <template v-slot:header>
                            <v-text-field
                                v-model="search"
                                clearable
                                density="comfortable"
                                hide-details
                                placeholder="Buscar"
                                prepend-inner-icon="mdi-magnify"
                                style="max-width: 300px"
                                variant="solo"
                            >
                            </v-text-field>
                        </template>
                        <template v-slot:default="{ items }">
                            <v-container class="pa-2" fluid>
                                <v-row dense>
                                    <v-col
                                        v-for="item in items"
                                        :key="item.nameCarrera"
                                        cols="auto"
                                        md="4"
                                    >
                                        <v-card class="pb-3" border flat>
                                            <v-list-item
                                                class="mb-2"
                                                :subtitle="
                                                    item.raw.asignaturaFA
                                                "
                                            >
                                                <template v-slot:title>
                                                    <strong
                                                        class="text-h6 mb-2"
                                                    >
                                                        {{
                                                            item.raw.nombreCurso
                                                        }}
                                                    </strong>
                                                </template>
                                            </v-list-item>
                                            <div
                                                class="d-flex justify-space-between px-4"
                                            >
                                                <div
                                                    class="d-flex align-center text-caption text-medium-emphasis me-1"
                                                >
                                                    <template
                                                        v-if="
                                                            item.raw
                                                                .tipo_FDoAP ===
                                                            1
                                                        "
                                                    >
                                                        <p
                                                            class="text-truncate"
                                                        >
                                                            Formación Docente
                                                        </p>
                                                    </template>
                                                    <template
                                                        v-if="
                                                            item.raw
                                                                .tipo_FDoAP ===
                                                            2
                                                        "
                                                    >
                                                        <p>
                                                            Actualización
                                                            Profesional
                                                        </p>
                                                    </template>
                                                </div>
                                            </div>
                                            <div
                                                class="d-flex justify-space-between px-4"
                                            >
                                                <div
                                                    class="d-flex align-center text-caption text-medium-emphasis me-1"
                                                >
                                                    <p class="text-truncate">
                                                        Dirigido a la academica
                                                        de
                                                        {{
                                                            item.raw.carrera
                                                                .nameCarrera
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div
                                                class="d-flex justify-space-between px-4"
                                            >
                                                <div
                                                    class="d-flex align-center text-caption text-medium-emphasis me-1"
                                                >
                                                    <template
                                                        v-if="
                                                            item.raw.estado ===
                                                            2
                                                        "
                                                    >
                                                        <strong
                                                            class="text-truncate"
                                                            >Finalizado</strong
                                                        >
                                                    </template>
                                                </div>
                                                <NavLink
                                                    :href="
                                                        route(
                                                            'show.inscritos.academicos',
                                                            item.raw.id
                                                        )
                                                    "
                                                    type="button"
                                                    as="button"
                                                >
                                                    <v-btn
                                                        border
                                                        flat
                                                        size="large"
                                                        class="text-none"
                                                        text="Ver"
                                                        prepend-icon="mdi-eye-arrow-right-outline"
                                                    >
                                                    </v-btn>
                                                </NavLink>
                                            </div>
                                        </v-card>
                                    </v-col>
                                </v-row>
                            </v-container>
                        </template>
                    </v-data-iterator>
                </template>
                <template v-else>
                    <div class="mt-2 mx-auto sm:px-6 lg:px-8 space-y-6">
                        <div
                            class="p-4 mt-2 sm:p-8 bg-white shadow sm:rounded-lg"
                        >
                            <v-alert
                                color="blue-darken-1"
                                icon="mdi-alert-circle"
                                prominent
                            >
                                Actualmente no hay cursos por realizarse, puede
                                visualizar todos los que se llevaron acabo al
                                presionar "Ver todos los registros".
                            </v-alert>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped></style>
