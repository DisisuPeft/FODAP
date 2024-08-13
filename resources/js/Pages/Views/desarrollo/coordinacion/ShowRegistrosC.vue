<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { computed, onMounted, ref } from "vue";
import NavLink from "@/Components/NavLink.vue";
import { Curso } from "@/store/curso.js";

const store = Curso();
const props = defineProps({
    cursos: Array,
    auth: Object,
    departamento: Array,
    carrera: Array,
    todas: Array,
});
const search = ref("");
const anio_filter = ref();
const departamento_filtro = ref();
const carrera_filter = ref();
const periodo = ref()
const tipo = ref()

const tipos_cursos = [
    { id: 1, text: "FORMACION DOCENTE" },
    { id: 2, text: "ACTUALIZACION PROFESIONAL" },
];
const period = [
    { id: 1, text: "ENERO-JUNIO" },
    { id: 2, text: "AGOSTO-DICIEMBRE" },
];

const filterCurso = computed(() => {
    const busqueda = search.value.toLowerCase().trim();
    const anio = anio_filter.value;
    const departamento = departamento_filtro.value;
    const carrera = carrera_filter.value;
    let formacion = tipo.value;
    let p = periodo.value;

    let cursosFiltrados = [...props.cursos];

    if (busqueda) {
        cursosFiltrados = cursosFiltrados.filter((item) => {
            return item.nombreCurso.toLowerCase().includes(busqueda);
        });
    }

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

    if (anio) {
        cursosFiltrados = cursosFiltrados.filter((item) => {
            const parse_anio = new Date(item.fecha_I).getFullYear();
            return parse_anio === anio;
        });
    }
    if (departamento) {
        cursosFiltrados = cursosFiltrados.filter((item) => {
            return item.id_departamento === departamento;
        });
    }
    if (carrera) {
        cursosFiltrados = cursosFiltrados.filter((item) => {
            return item.carrera_dirigido === carrera;
        });
    }

    return cursosFiltrados;
});

// const filterCurso = computed(() => {
//     const busqueda = search_ap.value.toLowerCase().trim();
//     const anio = anio_filter_ap.value;
//     const departamento = departamento_filtro_ap.value;
//     const carrera = carrera_ap.value;
//     let formacion = tipo.value;
//     let p = periodo.value;
//
//     let cursosFiltrados = [...props.cursos_ap];
//
//     if (busqueda) {
//         cursosFiltrados = cursosFiltrados.filter((item) => {
//             return item.nombreCurso.toLowerCase().includes(busqueda);
//         });
//     }
//
//     if (formacion) {
//         cursosFiltrados = cursosFiltrados.filter((c) => {
//             // console.log(c.tipo_FDoAP, formacion);
//             return c.tipo_FDoAP === formacion;
//         });
//     }
//     if (p) {
//         cursosFiltrados = cursosFiltrados.filter((c) => {
//             return c.periodo === p;
//         });
//     }
//
//     if (anio) {
//         cursosFiltrados = cursosFiltrados.filter((item) => {
//             const parse_anio = new Date(item.fecha_I).getFullYear();
//             return parse_anio === anio;
//         });
//     }
//     if (departamento) {
//         cursosFiltrados = cursosFiltrados.filter((item) => {
//             return item.id_departamento === departamento;
//         });
//     }
//     if (carrera) {
//         cursosFiltrados = cursosFiltrados.filter((item) => {
//             return item.carrera_dirigido === carrera;
//         });
//     }
//
//     return cursosFiltrados;
// });

const fullYears = computed(() => {
    const maxYears = new Date().getFullYear() + 1;
    const minYears = maxYears - 7;
    const years = [];
    for (let i = maxYears; i >= minYears; i--) {
        years.push(i);
    }

    return years;
});

function devolver() {
    let cursos = [...props.cursos_fd, ...props.cursos_ap];
    let total = 0;
    for (let i = 0; i < cursos.length; i++) {
        total += cursos[i].docente_inscrito.length;
    }

    return total;
}

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

    store.get_curso_desarrollo();
});

// console.log(props.todas);
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Registro de todos los cursos que se llevaron acabo
            </h2>
            <NavLink :href="route('index.desarrollo.cursos')" as="button">
                <v-btn icon="mdi-arrow-left"></v-btn>
            </NavLink>
        </template>

        <div class="mt-2 mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 mt-2 sm:p-8 bg-white shadow sm:rounded-lg">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Cursos de Formación Docente
                </h2>
                <template v-if="props.cursos.length !== 0">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Buscar por nombre de curso</label>
                            <input class="mt-1 block w-full border-gray-300 rounded-md shadow-lg" v-model="search">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Filtrar por año</label>
                            <select id="level" class="mt-1 block w-full border-gray-300 rounded-md shadow-xl" v-model="anio_filter">
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
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Filtrar por departamento académico</label>
                            <select id="level" class="mt-1 block w-full border-gray-300 rounded-md shadow-xl" v-model="departamento_filtro">
                                <option></option>
                                <option
                                    v-for="d in props.departamento"
                                    :value="d.id"
                                    :key="d.id"
                                >
                                    {{ d.nameDepartamento }}
                                </option>
                            </select>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Filtrar por área académica</label>
                            <select id="level" class="mt-1 block w-full border-gray-300 rounded-md shadow-xl" v-model="carrera_filter">
                                <option></option>
                                <option
                                    v-for="c in props.carrera"
                                    :value="c.id"
                                    :key="c.id"
                                >
                                    {{ c.nameCarrera }}
                                </option>
                            </select>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Filtrar por periodo (enero-junio/agosto-diciembre)</label>
                            <select id="level" class="mt-1 block w-full border-gray-300 rounded-md shadow-xl" v-model="periodo">
                                <option></option>
                                <option
                                    v-for="p in period"
                                    :value="p.id"
                                    :key="p.id"
                                >
                                    {{ p.text }}
                                </option>
                            </select>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Filtrar por Formación docente o Actualización profesional</label>
                            <select id="level" class="mt-1 block w-full border-gray-300 rounded-md shadow-xl" v-model="tipo">
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
<!--                    <div class="grid grid-rows-1">-->
<!--                        <div class="flex justify-center mt-5 mb-5 ma-8">-->
<!--                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4">-->
<!--                                <div class="flex justify-center w-50 mt-6">-->
<!--                                    <v-text-field-->
<!--                                        variant="solo"-->
<!--                                        v-model="search"-->
<!--                                        label="Buscar por nombre de curso"-->
<!--                                        clearable-->
<!--                                    ></v-text-field>-->
<!--                                </div>-->
<!--                                <div class="grid grid-rows-2">-->
<!--                                    <div class="flex justify-start">-->
<!--                                        <label class="text-sm">-->
<!--                                            Filtrar por año-->
<!--                                        </label>-->
<!--                                    </div>-->
<!--                                    <div class="flex justify-center">-->
<!--                                        <select v-model="anio_filter_fd">-->
<!--                                            <option></option>-->
<!--                                            <option-->
<!--                                                v-for="an in fullYears"-->
<!--                                                :value="an"-->
<!--                                                :key="an"-->
<!--                                            >-->
<!--                                                {{ an }}-->
<!--                                            </option>-->
<!--                                        </select>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                                <div class="flex justify-center">-->
<!--                                    <div class="flex justify-center w-50 mt-6">-->
<!--                                        <v-select-->
<!--                                            variant="solo"-->
<!--                                            v-model="departamento_filtro_fd"-->
<!--                                            :items="props.departamento"-->
<!--                                            label="Filtrar por departamento"-->
<!--                                            item-value="id"-->
<!--                                            item-title="nameDepartamento"-->
<!--                                        ></v-select>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                                <div class="flex justify-start">-->
<!--                                    <div class="flex justify-center w-50 mt-6">-->
<!--                                        <v-select-->
<!--                                            variant="solo"-->
<!--                                            v-model="carrera_fd"-->
<!--                                            :items="props.carrera"-->
<!--                                            label="Filtrar por carrera"-->
<!--                                            item-value="id"-->
<!--                                            item-title="nameCarrera"-->
<!--                                        ></v-select>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
                    <v-virtual-scroll
                        :items="filterCurso"
                        height="600"
                        item-height="50"
                        class="mt-4"
                    >
                        <template v-slot:default="{ item }">
                            <v-list-item>
                                <template v-slot:prepend> </template>

                                <v-list-item-title>{{
                                    item.nombreCurso
                                }}</v-list-item-title>
                                <v-list-item-subtitle>
                                    {{ item.asignaturaFA }}
                                </v-list-item-subtitle>
                                <template v-slot:append>
                                    <NavLink
                                        :href="
                                            route(
                                                'index.desarrollo.inscritos',
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
                                Sin registros.
                            </v-alert>
                        </div>
                    </div>
                </template>
            </div>
<!--            <div class="p-4 mt-2 sm:p-8 bg-white shadow sm:rounded-lg">-->
<!--                <h2 class="font-semibold text-xl text-gray-800 leading-tight">-->
<!--                    Cursos de Actualización Profesional-->
<!--                </h2>-->
<!--                <template v-if="props.cursos_ap !== null">-->
<!--                    <div class="grid grid-cols-3">-->
<!--                        <div class="flex justify-center w-50 mt-6">-->
<!--                            <v-text-field-->
<!--                                variant="solo"-->
<!--                                v-model="search_ap"-->
<!--                                label="Buscar por nombre de curso"-->
<!--                            ></v-text-field>-->
<!--                        </div>-->
<!--                        <div class="flex justify-center">-->
<!--                            <div class="flex justify-center w-50 mt-6">-->
<!--                                <v-select-->
<!--                                    variant="solo"-->
<!--                                    v-model="anio_filter_ap"-->
<!--                                    :items="fullYears"-->
<!--                                    label="Filtrar por año"-->
<!--                                ></v-select>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                        <div class="flex justify-center">-->
<!--                            <div class="flex justify-center w-50 mt-6">-->
<!--                                <v-select-->
<!--                                    variant="solo"-->
<!--                                    v-model="departamento_filtro_ap"-->
<!--                                    :items="props.departamento"-->
<!--                                    label="Filtrar por departamento"-->
<!--                                    item-value="id"-->
<!--                                    item-title="nameDepartamento"-->
<!--                                ></v-select>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <div class="flex justify-start">-->
<!--                        <div class="flex justify-center w-50 mt-6">-->
<!--                            <v-select-->
<!--                                variant="solo"-->
<!--                                v-model="carrera_ap"-->
<!--                                :items="props.carrera"-->
<!--                                label="Filtrar por carrera"-->
<!--                                item-value="id"-->
<!--                                item-title="nameCarrera"-->
<!--                            ></v-select>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <v-virtual-scroll-->
<!--                        :items="filterCursoAP"-->
<!--                        height="300"-->
<!--                        item-height="50"-->
<!--                        class="mt-4"-->
<!--                    >-->
<!--                        <template v-slot:default="{ item }">-->
<!--                            <v-list-item>-->
<!--                                <template v-slot:prepend> </template>-->

<!--                                <v-list-item-title>{{-->
<!--                                    item.nombreCurso-->
<!--                                }}</v-list-item-title>-->
<!--                                <v-list-item-subtitle>-->
<!--                                    {{ item.asignaturaFA }}-->
<!--                                </v-list-item-subtitle>-->
<!--                                <template v-slot:append>-->
<!--                                    <NavLink-->
<!--                                        :href="-->
<!--                                            route(-->
<!--                                                'index.desarrollo.inscritos',-->
<!--                                                item.id-->
<!--                                            )-->
<!--                                        "-->
<!--                                        type="button"-->
<!--                                        as="button"-->
<!--                                    >-->
<!--                                        <v-btn-->
<!--                                            border-->
<!--                                            flat-->
<!--                                            size="small"-->
<!--                                            class="text-none"-->
<!--                                            text="Ver"-->
<!--                                            prepend-icon="mdi-eye-arrow-right-outline"-->
<!--                                        >-->
<!--                                        </v-btn>-->
<!--                                    </NavLink>-->
<!--                                </template>-->
<!--                            </v-list-item>-->
<!--                        </template>-->
<!--                    </v-virtual-scroll>-->
<!--                </template>-->
<!--                <template v-else>-->
<!--                    <div class="mt-2 mx-auto sm:px-6 lg:px-8 space-y-6">-->
<!--                        <div-->
<!--                            class="p-4 mt-2 sm:p-8 bg-white shadow sm:rounded-lg"-->
<!--                        >-->
<!--                            <v-alert-->
<!--                                color="blue-darken-1"-->
<!--                                icon="mdi-alert-circle"-->
<!--                                prominent-->
<!--                            >-->
<!--                                Sin registros.-->
<!--                            </v-alert>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </template>-->
<!--            </div>-->
        </div>
    </AuthenticatedLayout>
</template>

<style scoped></style>
