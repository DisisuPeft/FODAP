<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";
import { onMounted, ref } from "vue";
import DeteccionDialog from "@/Pages/Views/dialogs/DeteccionDialogPDF.vue";
import { FODAPStore } from "@/store/server.js";
import { Deteccion } from "@/store/Deteccion.js";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Loading from "@/Components/Loading.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Modal from "@/Components/Modal.vue";

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
const snackbar = ref(false);
const color = ref("");
const message = ref("");
const timeout = ref(0);
const search = ref();
const loading = ref(false);
const show = ref(false);

const snackEventActivator = () => {
    snackbar.value = true;
    message.value =
        "Parece que los recursos se han actualizado, por favor recarga la pagina";
    color.value = "warning";
    timeout.value = 5000;
    setTimeout(() => {
        snackbar.value = false;
    }, timeout.value);
};
const snackErrorActivator = () => {
    snackbar.value = true;
    message.value = "No se pudo procesar la solicitud";
    color.value = "error";
    timeout.value = 5000;
    setTimeout(() => {
        snackbar.value = false;
    }, timeout.value);
};
const snackSuccessActivator = () => {
    snackbar.value = true;
    message.value = "Procesado correctamente";
    color.value = "success";
    timeout.value = 5000;
    setTimeout(() => {
        snackbar.value = false;
    }, timeout.value);
};
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
            snackEventActivator();
        }
    );
    window.Echo.private("delete-deteccion").listen(
        "DeleteDeteccionEvent",
        (event) => {
            snackEventActivator();
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

const modal = (event) => {
    // console.log(event);
};

const pdfDeteccion = (form) => {
    loading.value = true;
    axios
        .get("/pdf/deteccion", {
            params: {
                anio: form.anio,
                carrera: form.carrera,
                periodo: form.periodo,
            },
        })
        .then((res) => {
            // cursos.value = res.data.cursos
            if (res.data.mensaje) {
                message.value = res.data.mensaje;
                pdf_dialog.value = false;
                show.value = true;
                loading.value = false;
            } else {
                const url = "/storage/Deteccion.pdf";
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute("download", "deteccion.pdf");
                document.body.appendChild(link);
                link.click();
                form.reset();
                message.value = "Documento generado con exito";
                timeout.value = 5000;
                color.value = "success";
                snackSuccess.value = true;
                loading.value = false;
            }
        })
        .catch((error) => {
            message.value =
                "¡Debe ingresar los datos para generar el documento!";
            color.value = "error";
            timeout.value = 5000;
            snackSuccess.value = true;
            loading.value = false;
        });
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
                </div>
            </template>
            <div class="flex justify-end mr-2 pr-2">
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
        <DeteccionDialog
            :carreras="props.carrera"
            v-model:modelValue="pdf_dialog"
            @update:modelValue="modal"
            @form:deteccion="pdfDeteccion"
        ></DeteccionDialog>

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
