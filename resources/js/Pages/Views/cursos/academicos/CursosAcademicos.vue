<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { computed, onMounted, ref, watch } from "vue";
import TablaCursoAcademico from "@/Pages/Views/cursos/tablas/TablaCursoAcademico.vue";
import { Head } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";
import { Curso } from "@/store/curso.js";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CustomSnackBar from "@/Components/CustomSnackBar.vue";

const curso_store = Curso();

const props = defineProps({
    cursos: Array,
    auth: Object,
});

const search = ref("");
const p = ref();
const a = ref();
const t = ref();
const message = ref("");
const color = ref();
const snackbar = ref(false);
const timeout = ref(0);
const snackEventActivator = () => {
    snackbar.value = true;
    message.value =
        "Parece que los recursos se han actualizado, por favor recarga la pagina";
    color.value = "warning";
    timeout.value = 5000;
};
const snackErrorActivator = () => {
    snackbar.value = true;
    message.value = "No se pudo procesar la solicitud";
    color.value = "error";
    timeout.value = 5000;
};
const snackSuccessActivator = () => {
    snackbar.value = true;
    message.value = "Procesado correctamente";
    color.value = "success";
    timeout.value = 5000;
};
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
    let formacion = t.value;
    let periodo = p.value;
    let anio = a.value;

    let cursosFiltrados = [...props.cursos];

    if (formacion) {
        cursosFiltrados = cursosFiltrados.filter((c) => {
            // console.log(c.tipo_FDoAP, formacion);
            return c.tipo_FDoAP === formacion;
        });
    }
    if (periodo) {
        cursosFiltrados = cursosFiltrados.filter((c) => {
            // console.log(c.periodo, formacion);
            return c.periodo === periodo;
        });
    }
    if (anio) {
        cursosFiltrados = cursosFiltrados.filter((c) => {
            const parse_anio = new Date(c.fecha_I).getFullYear();
            return parse_anio === anio;
        });
    }
    return cursosFiltrados;
});
onMounted(() => {
    window.Echo.private(`App.Models.User.${props.auth.user.id}`).notification(
        (notification) => {
            switch (notification.type) {
                case "App\\Notifications\\NewDeteccionNotification":
                    props.auth.usernotifications++;
                    break;
                case "App\\Notifications\\DeteccionEditadaNotification":
                    props.auth.usernotifications++;
                    break;
                case "App\\Notifications\\AceptadoNotification":
                    props.auth.usernotifications++;
                    break;
                case "App\\Notifications\\ObservacionNotification":
                    props.auth.usernotifications++;
                    break;
            }
        }
    );

    window.Echo.private("cursos-aceptados").listen(
        "CursosAceptados",
        (event) => {
            snackEventActivator();
        }
    );
});
watch(p, (newp) => {
    // console.log(p, newp);
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-medium text-gray-900">Cursos</h2>
        </template>
<!--        <CustomSnackBar-->
<!--            :message="message"-->
<!--            :color="color"-->
<!--            v-model="snackbar"-->
<!--            :timeout="timeout"-->
<!--            @update:modelValue="snackbar = $event"-->
<!--        />-->
<!--        <div class="flex justify-center mt-5 mb-5">-->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 ml-8 p-4">
                <div class="flex justify-center">
                    <div class="grid grid-rows-1">
                        <div class="flex justify-start">
                            <label class="text-sm">
                                Filtrar por tipo de curso (Formación docente o
                                Actualización profesional)
                            </label>
                        </div>
                        <div class="flex justify-start md:justify-center p-2">
                            <select v-model="t" class="bg-white">
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
                            <label class="text-sm"> Filtrar por periodo </label>
                        </div>
                        <div class="flex justify-center justify-start md:justify-center p-2">
                            <select v-model="p" class="bg-white">
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
                            <label class="text-sm"> Filtrar por año </label>
                        </div>
                        <div class="flex justify-start md:justify-center p-2">
                            <select v-model="a" class="bg-white">
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
<!--        </div>-->
        <template v-if="props.cursos.length !== 0">
            <div class="mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 mt-3 sm:p-8 bg-white shadow sm:rounded-lg">
                    <v-virtual-scroll
                        :items="filter"
                        height="300"
                        item-height="50"
                        class="mt-4"
                    >
                        <template v-slot:default="{ item }">
                            <v-list-item>
                                <template v-slot:prepend>
                                    <div
                                        class="d-flex align-center text-caption text-medium-emphasis me-1"
                                    >
                                        <template v-if="item.estado === 0">
                                            <v-chip
                                                variant="flat"
                                                color="warning"
                                                prepend-icon="$info"
                                            >
                                                Curso por realizar
                                            </v-chip>
                                        </template>
                                        <template v-else>
                                            <v-chip
                                                variant="flat"
                                                color="success"
                                                prepend-icon="$info"
                                            >
                                                En curso
                                            </v-chip>
                                        </template>
                                    </div>
                                </template>

                                <div class="ml-8">
                                    <v-list-item-title>{{
                                        item.nombreCurso
                                    }}</v-list-item-title>
                                    <v-list-item-subtitle>
                                        {{ item.departamento.nameDepartamento }}
                                    </v-list-item-subtitle>
                                    <v-list-item-action
                                        ><strong>{{
                                            item.jefe.nombre_completo
                                        }}</strong></v-list-item-action
                                    >
                                </div>
                                <template v-slot:append>
                                    <NavLink
                                        :href="
                                            route(
                                                'show.inscritos.academicos',
                                                item.id
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
                                </template>
                            </v-list-item>
                        </template>
                    </v-virtual-scroll>
                </div>
                <div class="grid grid-cols-2 mt-5">
                    <div class="flex justify-end">
                        <NavLink
                            v-if="props.cursos.prev_page_url"
                            :href="props.cursos.prev_page_url"
                            as="button"
                        >
                            <primary-button>Anterior</primary-button>
                        </NavLink>
                    </div>
                    <div class="flex justify-start">
                        <NavLink
                            v-if="props.cursos.next_page_url"
                            :href="props.cursos.next_page_url"
                            as="button"
                        >
                            <primary-button>Siguiente</primary-button>
                        </NavLink>
                    </div>
                </div>
            </div>
        </template>
        <template v-else>
            <div class="mt-2 mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 mt-2 sm:p-8 bg-white shadow sm:rounded-lg">
                    <v-alert
                        color="blue-darken-1"
                        icon="mdi-alert-circle"
                        height="100"
                    >
                        Actualmente no se han aceptado cursos.
                    </v-alert>
                </div>
            </div>
        </template>

        <v-container class="mt-3">
            <v-row justify="center">
                <v-col cols="12" align="center">
                    <NavLink
                        :href="route('index.registros')"
                        :active="route().current('index.registros')"
                        as="button"
                    >
                        <v-btn
                            rounded="xl"
                            block
                            size="x-large"
                            color="blue-darken-1"
                            prepend-icon="mdi-archive"
                        >
                            Todos los registros
                        </v-btn>
                    </NavLink>
                </v-col>
            </v-row>
        </v-container>
    </AuthenticatedLayout>
</template>

<style scoped></style>
