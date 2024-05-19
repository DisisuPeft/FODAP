<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import NavLink from "@/Components/NavLink.vue";
import {computed, onMounted, ref} from "vue";
import DeteccionDialog from '/resources/js/Pages/Views/dialogs/DeteccionDialogPDF.vue'
import {Deteccion} from "@/store/Deteccion.js";
import { TailwindPagination } from 'laravel-vue-pagination';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CustomSnackBar from "@/Components/CustomSnackBar.vue";
import {router} from "@inertiajs/vue3";
import Loading from "@/Components/Loading.vue";
import Modal from "@/Components/Modal.vue";

const store = Deteccion()
const props = defineProps({
    deteccionesFD: Array,
    deteccionesAP: Array,
    auth: Object,
    carrera: Array,
    errors: Object
});

const pdf_dialog = ref(false);
const timeout = ref(0)
const message = ref("")
const color = ref("")
const snackbar = ref(false)
const loading = ref(false)
const modal = ref(false)
const search = ref("");
const search2 = ref("");
const carrera = ref()
const carrera2= ref()

const filterCursoFD = computed(() => {
    const busqueda = search.value.toLowerCase().trim();
    const carer = carrera.value
    let cursosFiltrados = [...props.deteccionesFD];

    if (busqueda) {
        cursosFiltrados = cursosFiltrados.filter(item => {
            return item.nombreCurso.toLowerCase().includes(busqueda)
        });
    }

    if (carer) {
        cursosFiltrados = cursosFiltrados.filter(item => {
            return item.carrera_dirigido === carer
        });
    }

    return cursosFiltrados;
});
const filterCursoAP = computed(() => {
    const busqueda = search2.value.toLowerCase().trim();
    const carer = carrera2.value
    let cursosFiltrados = [...props.deteccionesAP];

    if (busqueda) {
        cursosFiltrados = cursosFiltrados.filter(item => {
            return item.nombreCurso.toLowerCase().includes(busqueda)
        });
    }

    if (carer) {
        cursosFiltrados = cursosFiltrados.filter(item => {
            return item.carrera_dirigido === carer
        });
    }

    return cursosFiltrados;
});

const snackEventActivator = () => {
    snackbar.value = true;
    message.value = "Parece que los recursos se han actualizado, por favor recarga la pagina"
    color.value = "warning"
    timeout.value = 5000
    setTimeout(() => {
        snackbar.value = false
    }, timeout.value)
};
const snackErrorActivator = () => {
    snackbar.value = true;
    message.value = "No se pudo procesar la solicitud"
    color.value = "error"
    timeout.value = 5000
    setTimeout(() => {
        snackbar.value = false
    }, timeout.value)
};
const snackSuccessActivator = () => {
    snackbar.value = true;
    message.value = "Procesado correctamente"
    color.value = "success"
    timeout.value = 5000
    setTimeout(() => {
        snackbar.value = false
    }, timeout.value)
};
const reloadPage = () => {
    router.reload();
    snackbar.value = false
}
onMounted(() => {
    window.Echo.private(`App.Models.User.${props.auth.user.id}`).notification((notification) => {
        switch (notification.type){
            case 'App\\Notifications\\NewDeteccionNotification':
                props.auth.usernotifications++
                break;
            case 'App\\Notifications\\DeteccionEditadaNotification':
                props.auth.usernotifications++
                break;
            case 'App\\Notifications\\AceptadoNotification':
                props.auth.usernotifications++
                break;
            case 'App\\Notifications\\ObservacionNotification':
                props.auth.usernotifications++
                break;
        }
    });


    window.Echo.private('deteccion_necesidades').listen('DeteccionEvent', (event) => {
        snackEventActivator()
    });
    window.Echo.private('delete-deteccion').listen('DeleteDeteccionEvent', (event) => {
        snackEventActivator()
    });
    window.Echo.private('deteccion-editada').listen('DeteccionEditadaEvent', (event) => {
        snackEventActivator()
    });
});
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
                pdf_dialog.value = false
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
                snackbar.value = true;
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
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Deteccion de Necesidades</h2>
        </template>
        <v-container class="mt-4">
            <v-row justify="start" class="ml-16">
                <v-btn color="blue-darken-1" rounded size="large" @click="pdf_dialog = true">Generar PDF</v-btn>
            </v-row>
        </v-container>
        <DeteccionDialog
            :carreras="props.carrera"
            v-model:modelValue="pdf_dialog"
            @update:modelValue="pdf_dialog = $event"
            @form:deteccion="pdfDeteccion"
        ></DeteccionDialog>
        <div class="mt-2 mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 mt-2 sm:p-8 bg-white shadow sm:rounded-lg">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Deteccion de Necesidades de Formación Docente</h2>
                <div class="grid grid-cols-2">
                    <div class="flex justify-center mt-2 w-50">
                        <v-text-field clearable label="Buscar" variant="solo" v-model="search"></v-text-field>
                    </div>
                    <div class="flex justify-center mt-2 w-50">
                        <v-select v-model="carrera" clearable label="Filtrar por carrera" variant="solo" :items="props.carrera" item-value="id" item-title="nameCarrera"></v-select>
                    </div>
                </div>
                <template v-if="props.deteccionesFD.length !== 0">
                    <v-virtual-scroll
                        :items="filterCursoFD"
                        height="300"
                        item-height="50"
                        class="mt-4"

                    >
                        <template v-slot:default="{ item }">
                            <v-list-item>
                                <template v-slot:prepend>

                                </template>

                                <v-list-item-title>{{ item.nombreCurso }}</v-list-item-title>
                                <v-list-item-subtitle>
                                    {{item.departamento.nameDepartamento}}
                                </v-list-item-subtitle>
                                <template v-if="item.jefe !== null">
                                    <v-list-item-action><strong>{{item.jefe.nombre_completo}}</strong></v-list-item-action>
                                </template>
                                <template v-else>
                                    <v-list-item-action><strong>Usuario sin nombre</strong></v-list-item-action>
                                </template>
                                <template v-slot:append>
                                    <NavLink :href="route('show.Cdetecciones', item.id)" type="button" as="button">
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
                        <div class="p-4 mt-2 sm:p-8 bg-white shadow sm:rounded-lg">
                            <v-alert
                                color="blue-darken-1"
                                icon="mdi-alert-circle"
                                prominent
                            >
                                Actualmente no hay cursos por realizarse, puede visualizar todos los que se llevaron acabo al presionar  "Ver todos los registros".
                            </v-alert>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <div class="mt-2 mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 mt-2 sm:p-8 bg-white shadow sm:rounded-lg">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Deteccion de Necesidades de Actualización Profesional</h2>
                <div class="grid grid-cols-2">
                    <div class="flex justify-center mt-2 w-50">
                        <v-text-field clearable label="Buscar" variant="solo" v-model="search2"></v-text-field>
                    </div>
                    <div class="flex justify-center mt-2 w-50">
                        <v-select v-model="carrera2" clearable label="Filtrar por carrera" variant="solo" :items="props.carrera" item-value="id" item-title="nameCarrera"></v-select>
                    </div>
                </div>
                <template v-if="props.deteccionesAP.length !== 0">
                    <v-virtual-scroll
                        :items="filterCursoAP"
                        height="300"
                        item-height="50"
                        class="mt-4"

                    >
                        <template v-slot:default="{ item }">
                            <v-list-item>
                                <template v-slot:prepend>

                                </template>

                                <v-list-item-title>{{ item.nombreCurso }}</v-list-item-title>
                                <v-list-item-subtitle>
                                    {{item.departamento.nameDepartamento}}
                                </v-list-item-subtitle>
                                <template v-if="item.jefe !== null">
                                    <v-list-item-action><strong>{{item.jefe.nombre_completo}}</strong></v-list-item-action>
                                </template>
                                <template v-else>
                                    <v-list-item-action><strong>Usuario sin nombre</strong></v-list-item-action>
                                </template>
                                <template v-slot:append>
                                    <NavLink :href="route('show.Cdetecciones', item.id)" type="button" as="button">
                                        <v-btn
                                            border
                                            flat
                                            size="small"
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
                        <div class="p-4 mt-2 sm:p-8 bg-white shadow sm:rounded-lg">
                            <v-alert
                                color="blue-darken-1"
                                icon="mdi-alert-circle"
                                prominent
                            >
                                Actualmente no hay cursos por realizarse, puede visualizar todos los que se llevaron acabo al presionar  "Ver todos los registros".
                            </v-alert>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <CustomSnackBar :message="message" :color="color" :timeout="timeout" v-model="snackbar" @update:modelValue="snackbar = $event">
            <template v-slot:reloadingbutton>
                <div class="flex justify-start pa-1">
                    <v-btn @click="reloadPage" icon="mdi-reload"></v-btn>
                </div>
            </template>
        </CustomSnackBar>
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
                            <i class="mdi mdi-close text-4xl" ></i>
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

<style scoped>

</style>
