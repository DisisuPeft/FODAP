<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {computed, onMounted, ref} from "vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {Link, router} from "@inertiajs/vue3";
import axios from "axios";
import { useForm } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import Loading from "@/Components/Loading.vue";
import CustomSnackBar from "@/Components/CustomSnackBar.vue";
import {errorMsg, notify, success_alert} from "@/jsfiels/alertas.js";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    cursos_anio: Number,
    cursos_periodos: Array,
    cursos_tipo: Array,
    docente_carrera: Array,
    total_cursos_ap_fd: Array,
    docentes_genero: Array
});
const showingNavigationDropdown = ref(false);
const showingNavigationDropdown2 = ref(false);
const showingNavigationDropdown3 = ref(false);
const showingNavigationDropdown4 = ref(false);
const loading = ref(false);
const message = ref("");
const modal = ref(false)


const close = () => {
    modal.value = false
}
const year = ref()

const request_anio = useForm({
    anio: null,
});

function download_excel_tipo() {
    loading.value = true;
    axios
        .get(route("excel.cursos.tipo"), {
            params: {
                year: year.value
            }
        })
        .then((res) => {
            loading.value = false;
            const url = "/storage/estadisticas_tipo.xlsx";
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", "estadisticas_tipo.xlsx");
            document.body.appendChild(link);
            link.click();
            success_alert('Exito!', 'Documento Excel descargado.')
        })
        .catch((error) => {
            loading.value = false;
            errorMsg('Error', 'Se debe de revisar en codigo.')
        });
}

function download_excel_periodos() {
    loading.value = true;
    axios
        .get(route("reporte.periodos"), {
            params: {
                year: year.value
            }
        })
        .then((res) => {
            loading.value = false;
            const url = "/storage/periodos.xlsx";
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", "periodos.xlsx");
            document.body.appendChild(link);
            link.click()
            success_alert('Exito!', 'Documento Excel descargado.')
        })
        .catch((error) => {
            loading.value = false;
            errorMsg('Error', 'Se debe de revisar en codigo.')
        });
}

function download_excel_docentes_capacitados() {
    loading.value = true;
    axios
        .get(route("reporte.docentes.capacitados"), {
            params: {
                year: year.value
            }
        })
        .then((res) => {
            loading.value = false;
            const url = "/storage/capacitados.xlsx";
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", "capacitados.xlsx");
            document.body.appendChild(link);
            link.click()
            success_alert('Exito!', 'Documento Excel descargado.')
        })
        .catch((error) => {
            loading.value = false;
            errorMsg('Error', 'Se debe de revisar en codigo.')
        });
}

function download_excel_FDAP() {
    loading.value = true;
    axios
        .get(route("reporte.FDAP"), {
            params: {
                year: year.value
            }
        })
        .then((res) => {
            loading.value = false;
            const url = "/storage/estadisticas_FDAP.xlsx";
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", "estadisticas_FDAP.xlsx");
            document.body.appendChild(link);
            link.click()
            success_alert('Exito!', 'Documento Excel descargado.')
        })
        .catch((error) => {
            loading.value = false;
            errorMsg('Error', 'Se debe de revisar en codigo.')
        });
}
const fullYears = computed(() => {
    const maxYears = new Date().getFullYear() + 1;
    const minYears = maxYears - 2;
    const years = [];

    for (let i = maxYears; i >= minYears; i--) {
        years.push(i);
    }

    return years;
});

const submit_anio = () => {
    console.log(year.value)
    router.visit(route('index.estadisticas'),
        {
            method: "get",
            data: {
                year: year.value,
            },
            replace: false,
            preserveState: true,
        }
    )
}

function get_documento(id){
    axios.get(route('formato.capacitados.docente'), {
        params: {
            id: id
        }
    }).then((res) => {
        loading.value = false;
        const url = "/storage/capacitadosDocentes.xlsx";
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", "capacitadosDocentes.xlsx");
        document.body.appendChild(link);
        link.click()
        success_alert('Exito!', 'Documento Excel descargado.')
    }).catch((error) => {
            loading.value = false;
            errorMsg('Error', 'Se debe de revisar en codigo.')
    });
}
// console.log(props.docente_carrera);
onMounted(() => {
    // notify('¡Atención', 'warning', 'Esta sección del sistema presenta errores en los calculos por lo que sigue en revisión. Cuando este correcto, esta alerta desparecera.')
    let date = new Date()
    year.value = date.getFullYear()
})
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-medium text-gray-900">Estadisticas</h2>
        </template>

        <div class="">
            <Loading v-model="loading" @update:loading="loading = $event">
                <v-fade-transition leave-absolute>
                    <v-progress-circular
                        v-if="loading"
                        color="info"
                        :size="64"
                        :width="7"
                        indeterminate
                    ></v-progress-circular>
                </v-fade-transition>
            </Loading>
        </div>
        <div>
            <div class="mt-2 mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 mt-2 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-3">
                        <div class="flex justify-start ml-16 p-5">
<!--                            <InputLabel-->
<!--                                for="anio"-->
<!--                                value="Seleccionar año de estadisticas"-->
<!--                            />-->
                            <p class="text-xl">
                                Seleccionar año de estadisticas
                            </p>
                        </div>
<!--                        <form @submit.prevent="submit_anio">-->
                            <div class="grid grid-cols-1 md:grid-cols-2">
                                <div class="flex justify-end">
                                    <select id="level" class="mt-1 block w-full border-gray-300 rounded-md shadow-xl" v-model="year">
                                        <option></option>
                                        <option
                                            v-for="y in fullYears"
                                            :value="y"
                                            :key="y"
                                        >
                                            {{ y }}
                                        </option>
                                    </select>
                                </div>
                                <div class="flex justify-start ml-10 items-center">
                                    <v-btn
                                        icon="mdi-calendar-search"
                                        color="blue-darken"
                                        @click="submit_anio"
                                    ></v-btn>
                                </div>
                            </div>
<!--                        </form>-->
                    </div>

                    <div
                        class="max-w-lg mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-7xl w-full hover:brightness-100 ma-10"
                    >
                        <div class="grid gap-5 grid-cols-1 md:grid-cols-2">
                            <div class="flex justify-center items-center">
                                <p class="text-center text-4xl ma-5">
                                    Cursos realizados en {{ year }}
                                </p>
                            </div>
                            <div class="flex justify-center items-center">
                                <p class="text-center font-bold text-4xl ma-5">
                                    {{ props.cursos_anio }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="max-w-lg mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-7xl w-full hover:brightness-100 ma-10"
                    >
                        <div class="flex justify-center">
                            <div
                                class="grid grid-cols-2 md:grid-cols-2"
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                            >
                                <p class="ma-10 text-center text-2xl">
                                    Total de "Tipos de curso"
                                </p>
                                <div
                                    class="flex justify-center items-center mt-2"
                                >
                                    <template v-if="!showingNavigationDropdown">
                                        <v-icon
                                            >mdi-arrow-down-drop-circle-outline</v-icon
                                        >
                                    </template>
                                    <template v-if="showingNavigationDropdown">
                                        <v-icon
                                            >mdi-arrow-up-drop-circle-outline</v-icon
                                        >
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div
                            :class="{
                                block: showingNavigationDropdown,
                                hidden: !showingNavigationDropdown,
                            }"
                        >
                            <div class="flex justify-end mr-10">
                                <v-btn
                                    prepend-icon="mdi-microsoft-excel"
                                    color="green-lighten-1"
                                    @click="download_excel_tipo"
                                >
                                    Generar Excel
                                </v-btn>
                            </div>
                            <div class="flex justify-center ma-10">
                                <table
                                    class="table-auto border-collapse border-2 border-black"
                                >
                                    <thead>
                                        <tr>
                                            <th class="border-2 border-black">
                                                Tipo de curso
                                            </th>
                                            <th
                                                class="border-2 border-black pa-2"
                                            >
                                                Total de "tipo de curso" en
                                                {{ year }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(c, i) in props.cursos_tipo"
                                            :key="i"
                                        >
                                            <td
                                                class="border-2 border-black pa-2"
                                            >
                                                <p
                                                    class="text-center text-xl text-gray-900 pa-5"
                                                >
                                                    {{ c.tipo }}
                                                </p>
                                            </td>
                                            <td class="border-2 border-black">
                                                <p
                                                    class="text-center font-bold text-2xl"
                                                >
                                                    {{ c.total }}
                                                </p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div
                        class="max-w-lg mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-7xl w-full hover:brightness-100 ma-10"
                    >
                        <div class="flex justify-center">
                            <div
                                class="grid grid-cols-2 md:grid-cols-2"
                                @click="
                                    showingNavigationDropdown2 =
                                        !showingNavigationDropdown2
                                "
                            >
                                <p class="ma-10 text-center text-2xl">
                                    Total de cursos por periodo (enero-junio /
                                    agosto-diciembre)
                                </p>
                                <div
                                    class="flex justify-center items-center mt-2"
                                >
                                    <template v-if="!showingNavigationDropdown">
                                        <v-icon
                                            >mdi-arrow-down-drop-circle-outline</v-icon
                                        >
                                    </template>
                                    <template v-if="showingNavigationDropdown">
                                        <v-icon
                                            >mdi-arrow-up-drop-circle-outline</v-icon
                                        >
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div
                            :class="{
                                block: showingNavigationDropdown2,
                                hidden: !showingNavigationDropdown2,
                            }"
                        >
                            <div class="flex justify-end mr-16">
                                <v-btn
                                    prepend-icon="mdi-microsoft-excel"
                                    color="green-lighten-1"
                                    @click="download_excel_periodos"
                                >
                                    Generar Excel
                                </v-btn>
                            </div>
                            <div class="flex justify-center ma-10">
                                <table
                                    class="table-auto border-collapse border-2 border-black"
                                >
                                    <thead>
                                        <tr>
                                            <th class="border-2 border-black">
                                                Periodo
                                            </th>
                                            <th
                                                class="border-2 border-black pa-2"
                                            >
                                                Total de cursos {{ year }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(
                                                p, i
                                            ) in props.cursos_periodos"
                                            :key="i"
                                        >
                                            <td
                                                class="border-2 border-black pa-2"
                                            >
                                                <p
                                                    class="text-center text-xl text-gray-900 pa-5"
                                                >
                                                    {{ p.periodo }}
                                                </p>
                                            </td>
                                            <td class="border-2 border-black">
                                                <p
                                                    class="text-center font-bold text-2xl"
                                                >
                                                    {{ p.total }}
                                                </p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div
                        class="max-w-lg mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-7xl w-full hover:brightness-100 ma-10"
                    >
                        <div class="flex justify-center">
                            <div
                                class="grid grid-cols-2 md:grid-cols-2"
                                @click="
                                    showingNavigationDropdown3 =
                                        !showingNavigationDropdown3
                                "
                            >
                                <p class="ma-10 text-center text-2xl">
                                    Total de docentes capacitados por curso
                                    impartido en
                                    {{ year }}
                                </p>
                                <div
                                    class="flex justify-center items-center mt-2"
                                >
                                    <template v-if="!showingNavigationDropdown">
                                        <v-icon
                                            >mdi-arrow-down-drop-circle-outline</v-icon
                                        >
                                    </template>
                                    <template v-if="showingNavigationDropdown">
                                        <v-icon
                                            >mdi-arrow-up-drop-circle-outline</v-icon
                                        >
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div
                            :class="{
                                block: showingNavigationDropdown3,
                                hidden: !showingNavigationDropdown3,
                            }"
                        >
                            <div class="flex justify-center">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <template v-if="props.docentes_genero">
                                        <div class="flex justify-center mr-16">
                                            <v-btn
                                                color="red"
                                                @click="modal = true"
                                            >
                                                Genero N/E
                                            </v-btn>
                                        </div>
                                    </template>
                                    <Modal :show="modal" @close="close">
                                        <div class="grid grid-rows-1">
                                            <div class="flex justify-center">
                                                <div class="grid grid-cols-1">
                                                    <p class="p-5 text-xl">Docentes sin genero establecido</p>
                                                    <div class="p-10">
                                                        <table class="border-collapse border border-slate-500">
                                                            <thead>
                                                            <tr>
                                                                <th class="border border-slate-600">Nombre</th>
                                                                <th class="border border-slate-600 p-2">Genero</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr v-for="docente in docentes_genero" :key="docente.id">
                                                                <td class="border border-slate-600 p-2">
                                                                    {{docente.nombre_completo}}
                                                                </td>
                                                                <td class="border border-slate-600 p-2 text-center">
                                                                    {{docente.sexo ? docente.sexo : 'Sin genero'}}
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </Modal>
                                    <div class="flex justify-center mr-16">
                                        <v-btn
                                            prepend-icon="mdi-microsoft-excel"
                                            color="green-lighten-1"
                                            @click="download_excel_docentes_capacitados"
                                        >
                                            Generar Excel
                                        </v-btn>
                                    </div>
                                </div>
                            </div>
                            <div class="ma-10 flex justify-center">
                                <table
                                    class="table-auto border-collapse border-2 border-black"
                                >
                                    <thead>
                                        <tr>
                                            <th class="border-2 border-black">
                                                Departamento
                                            </th>
                                            <th
                                                class="border-2 border-black pa-2"
                                            >
                                                Total de docentes capacitados en
                                                {{ year }}
                                            </th>
                                            <th
                                                class="border-2 border-black pa-2"
                                            >
                                                Total de docentes masculinos
                                            </th>
                                            <th
                                                class="border-2 border-black pa-2"
                                            >
                                                Total de docentes femeninos
                                            </th>
                                            <th
                                                class="border-2 border-black pa-2"
                                            >
                                                Generar Excel.
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(
                                                t, i
                                            ) in props.docente_carrera"
                                            :key="i"
                                        >
                                            <td class="border-2 border-black">
                                                <p
                                                    class="text-center text-xl text-gray-900 pa-2"
                                                >
                                                    {{ t.carrera }}
                                                </p>
                                            </td>
                                            <td class="border-2 border-black">
                                                <p
                                                    class="text-center font-bold text-2xl"
                                                >
                                                    {{ t.total }}
                                                </p>
                                            </td>
                                            <td class="border-2 border-black">
                                                <p
                                                    class="text-center font-bold text-2xl"
                                                >
                                                    {{
                                                        t.Total_de_hombres_capacitados
                                                    }}
                                                </p>
                                            </td>
                                            <td class="border-2 border-black">
                                                <p
                                                    class="text-center font-bold text-2xl"
                                                >
                                                    {{
                                                        t.Total_de_mujeres_capacitadas
                                                    }}
                                                </p>
                                            </td>
                                            <td class="border-2 border-black">
                                                <div class="flex justify-center p-2">
                                                    <v-btn
                                                        icon="mdi-microsoft-excel"
                                                        color="green-lighten-1"
                                                        @click="get_documento(t.departamento_id)"
                                                    >

                                                    </v-btn>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div
                        class="max-w-lg mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-7xl w-full hover:brightness-100 ma-10"
                    >
                        <div class="flex justify-center">
                            <div
                                class="grid grid-cols-2 md:grid-cols-2"
                                @click="
                                    showingNavigationDropdown4 =
                                        !showingNavigationDropdown4
                                "
                            >
                                <p class="ma-10 text-center text-2xl">
                                    Total de cursos de formación docente y
                                    actualización profesional en {{ year }}
                                </p>
                                <div
                                    class="flex justify-center items-center mt-2"
                                >
                                    <template v-if="!showingNavigationDropdown">
                                        <v-icon
                                            >mdi-arrow-down-drop-circle-outline</v-icon
                                        >
                                    </template>
                                    <template v-if="showingNavigationDropdown">
                                        <v-icon
                                            >mdi-arrow-up-drop-circle-outline</v-icon
                                        >
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div
                            :class="{
                                block: showingNavigationDropdown4,
                                hidden: !showingNavigationDropdown4,
                            }"
                        >
                            <div class="flex justify-end mr-10">
                                <v-btn
                                    prepend-icon="mdi-microsoft-excel"
                                    color="green-lighten-1"
                                    @click="download_excel_FDAP"
                                >
                                    Generar Excel
                                </v-btn>
                            </div>
                            <div class="ma-10 flex justify-center">
                                <table
                                    class="table-auto border-collapse border-2 border-black"
                                >
                                    <thead>
                                        <tr>
                                            <th class="border-2 border-black">
                                                Tipo
                                            </th>
                                            <th
                                                class="border-2 border-black pa-2"
                                            >
                                                Total en {{ year }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(
                                                t, i
                                            ) in props.total_cursos_ap_fd"
                                            :key="i"
                                        >
                                            <td class="border-2 border-black">
                                                <p
                                                    class="text-center text-xl text-gray-900 pa-2"
                                                >
                                                    {{ t.tipo }}
                                                </p>
                                            </td>
                                            <td class="border-2 border-black">
                                                <p
                                                    class="text-center font-bold text-2xl"
                                                >
                                                    {{ t.total }}
                                                </p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style></style>
