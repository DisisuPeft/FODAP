<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { computed, onMounted, ref } from "vue";
import DialogPIFAP from "@/Pages/Views/dialogs/DialogPIFAP.vue";
import NavLink from "@/Components/NavLink.vue";
import { Curso } from "@/store/curso.js";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {AlertLoading, errorMsg, notify, success_alert} from "@/jsfiels/alertas.js";
import {router} from "@inertiajs/vue3";
import Swal from "sweetalert2";

const curso_store = Curso();

const props = defineProps({
    cursos: Array,
    auth: Object,
    errors: Object,
    carrera: Array,
    departamento: Array
});

const pdf_dialog = ref(false);
const search = ref("");
const message = ref("")
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
const estado = computed(() => {
    if (props.cursos.length === 0) {
        return 1;
    } else {
        return 0;
    }
});
const setColor = (state) => {
    if (state === 0) {
        return "warning";
    } else {
        return "success";
    }
};
const headers = ref([
    {
        align: "start",
        key: "nombreCurso",
        sortable: false,
        title: "Nombre de los Cursos",
    },
    { key: "objetivoEvento", title: "Objetivo" },
    { key: "fechas", title: "Fecha de realizacion" },
    { key: "", title: "Lugar (presencial o virtual)" },
    { key: "horarios", title: "Horario" },
    { key: "total_horas", title: "No. de horas x Curso" },
    { key: "facilitadores", title: "Facilitador (a)" },
    { key: "dirigido", title: "Dirigido a:" },
]);

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

    curso_store.get_curso_desarrollo();
});
function download_excel_clave_curso() {
    AlertLoading('Generando documento...', 'Esto puede tardar unos minutos')
    axios
        .get(route("excel.claves.curso"))
        .then((res) => {
            const url = "/storage/claves_curso.xlsx";
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", "claves_curso.xlsx");
            document.body.appendChild(link);
            link.click();
            success_alert('Exito.', 'Documento excel generado.')
        })
        .catch((error) => {
            errorMsg('Atención', `El servidor respondio: ${error.response.data}`)
        });
}
function download_excel_clave_curso_validacion() {
    AlertLoading('Generando documento...', 'Esto puede tardar unos minutos')
    axios
        .get(route("excel.claves.curso.validacion"))
        .then((res) => {
            const url = "/storage/claves_validacion.xlsx";
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", "claves_validacion.xlsx");
            document.body.appendChild(link);
            link.click();
            success_alert('Exito.', 'Documento excel generado.')
        })
        .catch((error) => {
            errorMsg('Atención', `El servidor respondio: ${error.response.data}`)
        });
}
const format_errors = (errors) => {
    for (const errorsKey in errors) {
        message.value += errors[errorsKey]
    }
    return message.value.split('.').join('. ');
}
// console.log(props.cursos);

const claves_curso = () => {
    Swal.fire({
        title: 'Atención!',
        text: 'Esta acción solo se debe realizar al finalizar los cursos cada periodo intersemestral. ¿Desea realizar la asignación de las claves en orden ascendente?',
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
        icon: "warning",
        timerProgressBar: true
    }).then(res => {
        if (res.isConfirmed){
            AlertLoading('Guardando los datos...', 'Esta accion puede tardar unos minutos')
            router.post(route('count.cursos'), {}, {
                onSuccess: () => {
                    success_alert('Exito.', 'Claves de cursos generadas.')
                },
                onError: () => {
                    errorMsg('Atención', `${format_errors(props.errors)}`)
                    message.value = ""
                },
            })
        }
    })
}
const filterCurso = computed(() => {
    const busqueda = search.value.toLowerCase().trim();
    // const anio = anio_filter.value;
    const departamento = departamento_filtro.value;
    const carrera = carrera_filter.value;
    let formacion = tipo.value;
    // let p = periodo.value;

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
    // if (p) {
    //     cursosFiltrados = cursosFiltrados.filter((c) => {
    //         return c.periodo === p;
    //     });
    // }

    // if (anio) {
    //     cursosFiltrados = cursosFiltrados.filter((item) => {
    //         const parse_anio = new Date(item.fecha_I).getFullYear();
    //         return parse_anio === anio;
    //     });
    // }
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
const fullYears = computed(() => {
    const maxYears = new Date().getFullYear() + 1;
    const minYears = maxYears - 7;
    const years = [];
    for (let i = maxYears; i >= minYears; i--) {
        years.push(i);
    }

    return years;
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-medium text-gray-900">Cursos</h2>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 ma-5">
            <div class="flex justify-center">
                <v-btn
                    prepend-icon="mdi-file-pdf-box"
                    size="large"
                    @click="pdf_dialog = true"
                    color="blue-darken-1"
                >
                    Generar PDF PIFDAP
                </v-btn>
            </div>
            <div class="flex justify-end">
                <v-btn
                    prepend-icon="mdi-microsoft-excel"
                    color="green-lighten-1"
                    @click="download_excel_clave_curso"
                >
                    Generar Claves de Curso
                </v-btn>
            </div>
            <div class="flex justify-start">
                <v-btn
                    prepend-icon="mdi-microsoft-excel"
                    color="green-lighten-1"
                    @click="download_excel_clave_curso_validacion"
                >
                    Generar Claves de validación
                </v-btn>
            </div>
        </div>
        <DialogPIFAP
            v-model="pdf_dialog"
            @update:modelValue="pdf_dialog = $event"
        ></DialogPIFAP>
        <div class="mt-2 mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 mt-2 sm:p-8 bg-white shadow sm:rounded-lg">
                <template v-if="props.cursos.length !== 0">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Buscar por nombre de curso</label>
                            <input class="mt-1 block w-full border-gray-300 rounded-md shadow-lg" v-model="search">
                        </div>
<!--                        <div class="col-span-1">-->
<!--                            <label class="block text-sm font-medium text-gray-700">Filtrar por año</label>-->
<!--                            <select id="level" class="mt-1 block w-full border-gray-300 rounded-md shadow-xl" v-model="anio_filter">-->
<!--                                <option></option>-->
<!--                                <option-->
<!--                                    v-for="an in fullYears"-->
<!--                                    :value="an"-->
<!--                                    :key="an"-->
<!--                                >-->
<!--                                    {{ an }}-->
<!--                                </option>-->
<!--                            </select>-->
<!--                        </div>-->
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
<!--                        <div class="col-span-1">-->
<!--                            <label class="block text-sm font-medium text-gray-700">Filtrar por periodo (enero-junio/agosto-diciembre)</label>-->
<!--                            <select id="level" class="mt-1 block w-full border-gray-300 rounded-md shadow-xl" v-model="periodo">-->
<!--                                <option></option>-->
<!--                                <option-->
<!--                                    v-for="p in period"-->
<!--                                    :value="p.id"-->
<!--                                    :key="p.id"-->
<!--                                >-->
<!--                                    {{ p.text }}-->
<!--                                </option>-->
<!--                            </select>-->
<!--                        </div>-->
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
                    <v-virtual-scroll
                        :items="filterCurso"
                        height="800"
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
                                        <template v-else-if="item.estado === 1">
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
                                    <template v-if="item.jefe !== null">
                                        <v-list-item-action
                                            ><strong>{{
                                                item.jefe.nombre_completo
                                            }}</strong></v-list-item-action
                                        >
                                    </template>
                                    <template v-else>
                                        <v-list-item-action
                                            ><strong
                                                >Usuario sin nombre</strong
                                            ></v-list-item-action
                                        >
                                    </template>
                                </div>
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
                                Actualmente no hay cursos por realizarse, puede
                                visualizar todos los que se llevaron acabo al
                                presionar "Ver todos los registros".
                            </v-alert>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="mt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div
                    class="grid gap-5 grid-cols-1 md:grid-cols-1 lg:grid-cols-2"
                >
                    <div class="flex justify-center items-center">
                        <NavLink :href="route('index.registros.c')" as="button">
                            <v-btn
                                block
                                size="large"
                                color="blue-darken-1"
                                prepend-icon="mdi-archive"
                                height="50"
                                width="550"
                            >
                                Ver todos los registros
                            </v-btn>
                        </NavLink>
                    </div>
                    <div class="flex justify-center">
                        <NavLink :href="route('create.curso')" as="button">
                            <v-btn
                                block
                                size="large"
                                color="blue-darken-1"
                                prepend-icon="mdi-pencil"
                                height="50"
                                width="550"
                            >
                                Crear Curso
                            </v-btn>
                        </NavLink>
                    </div>
                    <div class="flex justify-center ml-16">
<!--                        <NavLink-->
<!--                            :href="route('count.cursos')"-->
<!--                            method="post"-->
<!--                            as="button"-->
<!--                        >-->
                            <v-btn
                                block
                                size="large"
                                color="blue-darken-1"
                                prepend-icon="mdi-key-variant"
                                height="50"
                                width="550"
                                @click.prevent="claves_curso"
                            >
                                Claves de curso
                            </v-btn>
<!--                        </NavLink>-->
                        <div class="flex justify-start ml-5">
                            <v-tooltip location="right">
                                <template v-slot:activator="{ props }">
                                    <v-btn
                                        icon
                                        color="warning"
                                        v-bind="props"
                                        size="small"
                                    >
                                        <v-icon> mdi-help </v-icon>
                                    </v-btn>
                                </template>
                                <span
                                    >Esto unicamente debe ser ejecutado despues de finalizar todos los cursos.</span
                                >
                            </v-tooltip>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped></style>
