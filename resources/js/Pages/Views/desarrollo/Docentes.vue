<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { onMounted } from "vue";
import NavLink from "@/Components/NavLink.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { ref, computed } from "vue";
import CreateDocenteA from "@/Pages/Views/academicos/docentes/CreateDocenteA.vue";
import Loading from "@/Components/Loading.vue";
import DeleteModal from "@/Components/DeleteModal.vue";
import CustomSnackBar from "@/Components/CustomSnackBar.vue";
import { router } from "@inertiajs/vue3";
import { el } from "vuetify/locale";
import Swal from "sweetalert2";
import { AlertLoading, errorMsg, success_alert } from "@/jsfiels/alertas.js";

const props = defineProps({
    auth: Object,
    docentes: Array,
    posgrado: {
        type: Array,
    },
    puesto: {
        type: Array,
    },
    tipo_plaza: {
        type: Array,
    },
    carrera: Array,
    departamento: Array,
    errors: null,
    data: Number,
    totales: Array,
});

// console.log(props.data);

const search = ref("");
const dialog = ref(false);
const loading = ref(false);
const message = ref("");
const eliminar = ref(false);
let docente_id = ref(null);

const header = [
    { key: "nombre", title: "Nombre" },
    { key: "apellidoPat", title: "Apellido Paterno" },
    { key: "apellidoMat", title: "Apellido Materno" },
    { key: "email", title: "Correo institucional" },
    { key: "sexo", title: "Género" },
    { key: "departamento", title: "Departamento adscrito" },
    { key: "options", title: "Editar" },
    { key: "delete", title: "Eliminar" },
];

async function submitDocente(form) {
    dialog.value = false;
    Swal.fire({
        title: "¿Ingreso la información correcta?",
        text: "Esta acción se puede revertir",
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: "Confirmar",
        cancelButtonText: "Cancelar",
        icon: "info",
        timerProgressBar: true,
    }).then((res) => {
        if (res.isConfirmed) {
            AlertLoading(
                "Guardando la información...",
                "Esto puede tardar unos minutos"
            );
            form.post(route("store.docentes", "desarrollo"), {
                onSuccess: () => {
                    form.reset();
                    dialog.value = false;
                    success_alert("Exito", "El docente se creo.");
                },
                onError: () => {
                    dialog.value = false;
                    errorMsg("Atención", `${format_errors(props.errors)}`);
                    message.value = "";
                },
            });
        }
    });
}
async function DeleteDocente(id) {
    eliminar.value = false;
    Swal.fire({
        title: "Esta por eliminar el docente.",
        text: "Esta acción no se puede revertir",
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: "Confirmar",
        cancelButtonText: "Cancelar",
        icon: "info",
        timerProgressBar: true,
    }).then((res) => {
        if (res.isConfirmed) {
            AlertLoading(
                "Guardando la información...",
                "Esto puede tardar unos minutos"
            );
            router.delete(route("delete.docentesDa", id), {
                onSuccess: () => {
                    eliminar.value = false;
                    success_alert("Exito", "El docente se elimino.");
                    message.value = "";
                },
                onError: () => {
                    eliminar.value = false;
                    errorMsg("Atención", `${format_errors(props.errors)}`);
                    message.value = "";
                },
            });
        }
    });
}

function DropOut(id) {
    eliminar.value = false;
    docente_id.value = id;
    eliminar.value = true;
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
});

const filter = computed(() => {
    let docente = search.value.trim().toLowerCase();
    // console.log(docente)
    // Si no hay valor en el input, retorna todos los docentes
    if (!docente) {
        return props.docentes;
    }

    // Filtra docentes por el nombre completo
    return props.docentes.filter((c) => {
        return c.nombre_completo.toLowerCase().includes(docente);
    });
});

const format_errors = (errors) => {
    for (const errorsKey in errors) {
        message.value += errors[errorsKey];
    }
    return message.value.split(".").join(". ");
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-medium text-gray-900">Docentes</h2>
        </template>
        <div class="grid grid-cols-2 mt-5 ml-16">
            <div class="flex justify-center">
                <v-btn
                    prepend-icon="mdi-plus"
                    @click="dialog = true"
                    color="blue-darken-1"
                >
                    Agregar docente
                </v-btn>
                <create-docente-a
                    :tipo_plaza="props.tipo_plaza"
                    :puesto="props.puesto"
                    :posgrado="props.posgrado"
                    :departamento="props.departamento"
                    :carrera="props.carrera"
                    :auth="props.auth"
                    v-model="dialog"
                    @docente-add="submitDocente"
                    @update:modelValue="dialog = $event"
                >
                </create-docente-a>
            </div>
        </div>
        <div class="mt-5 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <v-text-field
                    v-model="search"
                    label="Buscar"
                    prepend-icon="mdi-magnify"
                    variant="solo"
                ></v-text-field>
                <v-data-table
                    :headers="header"
                    :items="filter"
                    fixed-header
                    next-icon="mdi-arrow-right-bold-circle"
                    prev-icon="mdi-arrow-left-bold-circle"
                    items-per-page="5"
                    items-per-page-text="Paginas"
                >
                    <template v-slot:item.email="{ item }">
                        <template v-if="item.usuario">
                            <v-chip color="success">
                                {{ item.usuario?.email }}
                            </v-chip>
                        </template>
                        <template v-if="!item.usuario">
                            <v-chip color="warning"> SIN REGISTRAR </v-chip>
                        </template>
                    </template>
                    <template v-slot:item.sexo="{ item }">
                        <template v-if="item.sexo === 1">
                            <p>MASCULINO</p>
                        </template>
                        <template v-else-if="item.sexo === 2">
                            <p>FEMENINO</p>
                        </template>
                        <template v-else>
                            <p>SIN GENERO SELECCIONADO</p>
                        </template>
                    </template>
                    <template v-slot:item.departamento="{ item }">
                        <template v-if="item.departamento !== null">
                            <p>{{ item.departamento.nameDepartamento }}</p>
                        </template>
                        <template v-else>
                            <p></p>
                        </template>
                    </template>
                    <template v-slot:item.options="{ item }">
                        <NavLink
                            :href="route('edit.docentes', item.id)"
                            as="button"
                        >
                            <v-btn icon size="large" elevation="0">
                                <v-icon>mdi-pencil</v-icon>
                            </v-btn>
                        </NavLink>
                    </template>
                    <template v-slot:item.delete="{ item }">
                        <v-btn
                            icon
                            size="large"
                            elevation="0"
                            @click="DropOut(item.id)"
                        >
                            <v-icon>mdi-delete-forever</v-icon>
                        </v-btn>
                    </template>
                </v-data-table>
            </div>
        </div>

        <DeleteModal
            :item="docente_id"
            v-model="eliminar"
            @update:modelValue="eliminar = $event"
            @delete:item="DeleteDocente"
        >
            <template #title>
                ¿Esta seguro que desea eliminar este docente?
            </template>

            <template #cancelText> Cancelar </template>
        </DeleteModal>

        <Loading v-model="loading" @update:modelValue="loading = $event">
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
    </AuthenticatedLayout>
</template>

<style scoped></style>
