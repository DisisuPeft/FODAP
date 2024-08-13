<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {onMounted} from "vue";
import NavLink from "@/Components/NavLink.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {ref, computed} from 'vue'
import CreateDocenteA from "@/Pages/Views/academicos/docentes/CreateDocenteA.vue";
import CustomSnackBar from "@/Components/CustomSnackBar.vue";
import {AlertLoading, errorMsg, success_alert} from "@/jsfiels/alertas.js";
import Swal from "sweetalert2";

const props = defineProps({
    auth: Object,
    docentes: Array,
    tipo_plaza: {
        type: Array,
    },
    puesto: {
        type: Array
    },
    carrera: {
        type: Array,
    },
    posgrado: {
        type: Array
    },
    departamento: {
        type: Array
    }
});

const search = ref("");
const dialogDocente = ref(false)
const message = ref("")
const color = ref("")
const snack_bar = ref(false)
const timeout = ref(5000)


const header = [
    {key: "nombre", title: "Nombre"},
    {key: "apellidoPat", title: "Apellido Paterno"},
    {key: "apellidoMat", title: "Apellido Materno"},
    {key: "options", title: "Opciones"},
    // {key: "delete", title: "Eliminar"},
];

async function submitDocente(form){
    dialogDocente.value = false
    AlertLoading('Guardando la información...', 'Esto puede tardar unos minutos')
        form.post(route('create.docentes.academicos.up', "academicos"), {
            onSuccess: () => {
                form.reset()
                success_alert('Exito.', 'Docente creado.')
            },
            onError: () => {
                errorMsg('Atención.', `${format_errors(props.errors)}`)
            }
        })
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
});
const format_errors = (errors) => {
    for (const errorsKey in errors) {
        message.value += errors[errorsKey]
    }
    return message.value.split('.').join('. ');
}

const filter = computed(() => {
    let docente = search.value.trim().toLowerCase();
    console.log(docente)
    // Si no hay valor en el input, retorna todos los docentes
    if (!docente) {
        return props.docentes;
    }

    // Filtra docentes por el nombre completo
    return props.docentes.filter((c) => {
        return c.nombre_completo.toLowerCase().includes(docente);
    });
});
</script>

<template>
<AuthenticatedLayout>
    <template #header>
        <h2 class="text-lg font-medium text-gray-900">Docentes</h2>
    </template>
    <div class="grid grid-cols-3 mt-5 ml-16">
        <div class="flex justify-center">
            <v-btn prepend-icon="mdi-plus" @click="dialogDocente = true">
                Agregar docente
            </v-btn>
            <create-docente-a
                :auth="props.auth"
                :carrera="props.carrera"
                :departamento="props.departamento"
                :tipo_plaza="props.tipo_plaza"
                :posgrado="props.posgrado"
                :puesto="props.puesto"
                v-model="dialogDocente"
                @update:modelValue="dialogDocente = $event"
                @docente-add="submitDocente"
            >

            </create-docente-a>
        </div>
    </div>
    <div class="mt-5 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
            <div class="mt-5 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">

                    <v-text-field
                        v-model="search"
                        label="Buscar"
                        prepend-icon="mdi-magnify"
                        variant="solo"
                    ></v-text-field>
                    <v-data-table :headers="header" :items="filter"
                                  fixed-header
                                  next-icon="mdi-arrow-right-bold-circle"
                                  prev-icon="mdi-arrow-left-bold-circle"
                                  items-per-page="5"
                                  items-per-page-text="Paginas"
                    >
                        <template v-slot:no-data>
                            <v-alert :value="true" color="warning">
                                <v-icon left color="white">warning</v-icon
                                >No se encontraron datos
                            </v-alert>
                        </template>
                        <template v-slot:item.options="{item}">
                            <NavLink :href="route('edit.docentes.academicos', item.id)" as="button">
                                <v-btn icon size="large" elevation="0">
                                    <v-icon>mdi-pencil</v-icon>
                                </v-btn>
                            </NavLink>
                        </template>
    <!--                        <template v-slot:item.delete="{item}">-->
    <!--&lt;!&ndash;                            <NavLink :href="route('edit.docentes.academicos', item.id)" as="button">&ndash;&gt;-->
    <!--                                <v-btn icon size="large" elevation="0">-->
    <!--                                    <v-icon>mdi-delete</v-icon>-->
    <!--                                </v-btn>-->
    <!--&lt;!&ndash;                            </NavLink>&ndash;&gt;-->
    <!--                        </template>-->
                    </v-data-table>
                </div>
            </div>
        </div>
    </div>
    <CustomSnackBar :timeout="timeout" :color="color" :message="message" v-model="snack_bar" @update:modelValue="snack_bar = $event"></CustomSnackBar>
</AuthenticatedLayout>
</template>

<style scoped>

</style>
