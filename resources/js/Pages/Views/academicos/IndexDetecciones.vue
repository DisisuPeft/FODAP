<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";
import { computed, onMounted, ref } from "vue";
import DeteccionDialog from "@/Pages/Views/dialogs/DeteccionDialogPDF.vue";
import { FODAPStore } from "@/store/server.js";
import { Deteccion } from "@/store/Deteccion.js";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Loading from "@/Components/Loading.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Modal from "@/Components/Modal.vue";
import {
    AlertLoading,
    errorMsg,
    notify,
    success_alert,
} from "@/jsfiels/alertas.js";

const store = FODAPStore();
const detecciones_store = Deteccion();
const props = defineProps({
    detecciones: {
        type: Array,
    },
    carrera: {
        type: Array,
    },
    auth: Object,
    dates: Array,
});
const pdf_dialog = ref(false);
const color = ref("");
const message = ref("");
const search = ref();
const loading = ref(false);
const show = ref(false);

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
    // store.if_enable_fechas()
    detecciones_store.deteccionesAcademico();
    window.Echo.private("dates-enable").listen("DatesEnableEvent", (event) => {
        // console.log(event.dates.fechas)
        store.update_enable_dates(event.dates.fechas);
        localStorage.setItem(
            "dates_enable",
            JSON.stringify(event.dates.fechas)
        );
    });
    window.Echo.private("deteccion-observacion").listen(
        "ObservacionEvent",
        (event) => {
            notify(
                "Atención",
                "info",
                "Los recursos han cambiado, ACTUALIZA LA PAGINA"
            );
        }
    );
    window.Echo.private("delete-deteccion").listen(
        "DeleteDeteccionEvent",
        (event) => {
            notify(
                "Atención",
                "info",
                "Los recursos han cambiado, ACTUALIZA LA PAGINA"
            );
        }
    );

    const storedDatesEnable = localStorage.getItem("dates_enable");
    if (storedDatesEnable) {
        // Si hay un valor en localStorage, conviértelo de nuevo a objeto JavaScript
        const parsedDatesEnable = JSON.parse(storedDatesEnable);
        // console.log(parsedDatesEnable)
        // Usa parsedDatesEnable para inicializar el estado en tu store de Pinia
        store.update_enable_dates(parsedDatesEnable);
    }
});

const closeModal = () => {
    pdf_dialog.value = false;
};

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

const pdfDeteccion = () => {
    AlertLoading("Generando documento...", "Esto puede tardar unos minutos");
    // loading.value = true
    axios
        .get("/pdf/deteccion", {
            params: {
                anio: form.anio,
                carrera: form.carrera,
                periodo: form.periodo,
                tipo_documento: "Deteccion de necesidades",
            },
        })
        .then((res) => {
            // cursos.value = res.data.cursos
            const url = "/storage/Deteccion.pdf";
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", "deteccion.pdf");
            document.body.appendChild(link);
            link.click();
            loading.value = false;
            form.reset();
            pdf_dialog.value = false;
            success_alert("Exito", "El documento se descargo.");
        })
        .catch((error) => {
            pdf_dialog.value = false;
            loading.value = false;
            errorMsg(
                "Atención",
                `${format_errors(error.response?.data.errors)}`
            );
            message.value = "";
        });
};

const format_errors = (errors) => {
    for (const errorsKey in errors) {
        message.value += errors[errorsKey];
    }
    return message.value.split(".").join(". ");
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Detecciones" />
        <template #header>
            <h2 class="text-lg font-medium text-gray-900">
                Deteccion de Necesidades
            </h2>

            <div v-if="props.dates !== null">
                <template v-if="props.dates[0] === true">
                    <NavLink
                        :href="route('detecciones.create')"
                        :active="route().current('detecciones.create')"
                        as="button"
                    >
                        <v-btn
                            prepend-icon="mdi-pen-plus"
                            rounded="xl"
                            color="blue-darken-1"
                            >CREAR DETECCION DE NECESIDADES</v-btn
                        >
                    </NavLink>
                </template>
            </div>
        </template>

        <div class="grid grid-cols-2 mt-4 mb-4">
            <template v-if="props.detecciones.length === 0">
                <div class="flex justify-center ml-5 pl-5">
                    <v-btn
                        @click="pdf_dialog = true"
                        prepend-icon="mdi-file-pdf-box"
                        color="blue-darken-1"
                        rounded="xl"
                        width="400"
                    >
                        Generar PDF
                    </v-btn>
                    <Modal :show="pdf_dialog" @close="closeModal">
                        <div class="p-6 rounded-lg shadow-xl max-w-md mx-auto">
                            <h2 class="text-2xl font-bold text-gray-700 mb-6">
                                Generar PDF
                            </h2>
                            <form
                                @submit.prevent="pdfDeteccion"
                                class="space-y-6"
                            >
                                <div class="space-y-4">
                                    <div class="form-group">
                                        <label
                                            for="carrera"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Carrera a la que va dirigida:
                                        </label>
                                        <v-select
                                            v-model="form.carrera"
                                            :items="props.carrera"
                                            item-title="nameCarrera"
                                            item-value="id"
                                            variant="outlined"
                                            class="rounded-md"
                                        ></v-select>
                                    </div>

                                    <div class="form-group">
                                        <label
                                            for="periodo"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Periodo:
                                        </label>
                                        <v-select
                                            v-model="form.periodo"
                                            :items="periodos"
                                            item-title="name"
                                            item-value="id"
                                            variant="outlined"
                                            class="rounded-md"
                                        ></v-select>
                                    </div>

                                    <div class="form-group">
                                        <label
                                            for="anio"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Año:
                                        </label>
                                        <v-select
                                            v-model="form.anio"
                                            :items="fullYears"
                                            item-title="name"
                                            item-value="id"
                                            variant="outlined"
                                            class="rounded-md"
                                        ></v-select>
                                    </div>
                                </div>

                                <div class="flex justify-between mt-8">
                                    <v-btn
                                        color="error"
                                        @click="pdf_dialog = false"
                                        class="px-4 py-2 rounded-md transition-colors duration-200 ease-in-out hover:bg-red-600"
                                    >
                                        Cancelar
                                    </v-btn>
                                    <v-btn
                                        type="submit"
                                        color="success"
                                        prepend-icon="mdi-file-pdf-box"
                                        class="px-4 py-2 rounded-md transition-colors duration-200 ease-in-out hover:bg-green-600"
                                    >
                                        Generar
                                    </v-btn>
                                </div>
                            </form>
                        </div>
                    </Modal>
                </div>
            </template>
            <div class="flex justify-end mr-2 pr-6">
                <div v-if="props.dates">
                    <template v-if="props.dates[0] === true">
                        <template v-if="props.dates[1].d === 1">
                            <v-alert color="warning" icon="$warning" prominent>
                                <strong class="text-center text-lg">
                                    Queda un {{ props.dates[1].d }} dia y
                                    {{ props.dates[1].h }} h para poder capturar
                                    Deteccion de Necesidades
                                </strong>
                            </v-alert>
                        </template>
                        <template v-if="props.dates[1].d === 0">
                            <v-alert color="error" icon="$error" prominent>
                                <strong class="text-center text-lg">
                                    Quedan {{ props.dates[1].h }} h para poder
                                    capturar Deteccion de Necesidades
                                </strong>
                            </v-alert>
                        </template>
                        <template v-if="props.dates[1].d > 1">
                            <v-alert color="info" icon="$info">
                                <strong class="text-center text-lg">
                                    Quedan {{ props.dates[1].d }} dias y
                                    {{ props.dates[1].h }} h para poder hacer su
                                    captura de Deteccion de Necesidades
                                </strong>
                            </v-alert>
                        </template>
                    </template>
                </div>
            </div>
        </div>

        <!--        dialog-->
        <!--        <DeteccionDialog-->
        <!--            :carreras="props.carrera"-->
        <!--            v-model:modelValue="pdf_dialog"-->
        <!--            @update:modelValue="modal"-->
        <!--            @form:deteccion="pdfDeteccion"-->
        <!--        ></DeteccionDialog>-->

        <template v-if="props.detecciones.length !== 0">
            <!--Tabla-->
            <div class="mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <v-virtual-scroll
                        :items="props.detecciones"
                        height="300"
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
                                    <template v-if="item.tipo_FDoAP === 1">
                                        FORMACIÓN DOCENTE
                                    </template>
                                    <template v-if="item.tipo_FDoAP === 2">
                                        ACTUALIZACIÓN PROFESIONAL
                                    </template>
                                </v-list-item-subtitle>
                                <!--                                <v-list-item-action><strong>{{item.jefe.nombre_completo}}</strong></v-list-item-action>-->
                                <template v-slot:append>
                                    <NavLink
                                        :href="
                                            route('show.detecciones', item.id)
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
            </div>
        </template>
        <template v-else>
            <div class="mt-2 mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 mt-2 sm:p-8 bg-white shadow sm:rounded-lg">
                    <v-alert
                        color="blue-darken-1"
                        icon="mdi-alert-circle"
                        prominent
                    >
                        Actualmente no se han capturado cursos.
                    </v-alert>
                </div>
            </div>
        </template>
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
        <Modal :show="show">
            <div class="grid grid-rows-1">
                <div class="flex justify-end">
                    <div class="flex justify-end ma-5">
                        <button class="rounded-full" @click="show = false">
                            <i class="mdi mdi-close text-4xl"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="flex justify-center">
                <i class="mdi mdi-alert-circle-outline text-6xl"></i>
            </div>
            <div class="flex justify-center">
                <p class="text-center text-4xl ma-10">{{ message }}</p>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
