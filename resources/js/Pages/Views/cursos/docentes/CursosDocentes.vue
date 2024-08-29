<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {computed, onMounted, ref, watch} from "vue";
import {Head, useForm} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {FODAPStore} from "@/store/server.js";
import NavLink from "@/Components/NavLink.vue";
import CustomSnackBar from "@/Components/CustomSnackBar.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Swal from "sweetalert2";
import {notify, success_alert, errorMsg} from "@/jsfiels/alertas.js";
// import 'sweetalert2/src/sweetalert2.scss'
import InfoDialog from "@/Components/InfoDialog.vue";
import {Curso} from "@/store/curso.js";

const store = Curso()

const props = defineProps({
    cursos: Array,
    auth: Object
});

const form = useForm({
    id_docente: props.auth.user.docente_id,
});
const alert = ref(false);
const search = ref("");
const color = ref("")
const message = ref("")
const snackbar = ref(false)
const timeout = ref(0);
const dialogInfo = ref(false)
const id_ref = ref(null)
let curso_selected = ref({})

const confirm_inscripcion = (item) => {
    // console.log(item)
    if (item.id_departamento !== props.auth.user.departamento_id){
        notify('Atención!', 'info', 'Este curso es externo a su departamento de adscripción. Acuda con el jefe del departamento que propuso el curso para que lo inscriba.')
    }else{
        Swal.fire({
            title: "¿Esta seguro que desea inscribirse a este curso?",
            text: "Esta accion no se puede revertir, si requiere lo contrario informar a la coordinación de formacion docente",
            showDenyButton: true,
            showConfirmButton: true,
            confirmButtonText: "Inscribirse",
            denyButtonText: `Cancelar`,
            icon: 'info',
        }).then(res => {
            if (res.isConfirmed){
                submit(item)
                // console.log(item)
                // notify('Alerta', 'info', 'Error al intentar inscribirse a este curso, notifique a desarrollo academico')
            }
        })
    }
}
const submit = (item) => {
    form.post(route('inscripcion.docente', item), {
        onSuccess: () => {
            success_alert('Exito', 'Te has inscrito con exito a este curso')
        },
        onError: () => {
            errorMsg('Error', 'Ha ocurrido un error al intentar inscribirse, notificar al area de desarrollo academico')
        }
    })
}
const id_curso = useForm({
    curso: null
})
// const desinscribirme = (id, item) => {
//     id_curso.curso = item
//     id_curso.post(route('mi.desinscrito', id), {
//         onSuccess: () => {
//             snackSuccessActivator()
//         },
//         onError: () => {
//             snackErrorActivator()
//         }
//     })
// }

function openDialog(curso){
    curso_selected.value = curso
    dialogInfo.value = true
}
watch(() => curso_selected.value, (newID) => {
    id_ref.value = newID

    store.infoCourse(id_ref.value.id)
});
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

    window.Echo.private("cursos-aceptados").listen("CursosAceptados", (event) => {
        // snackEventActivator()
    })
});
</script>

<template>
    <Head title="Cursos"/>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-medium text-gray-900">Cursos</h2>
        </template>
        <template v-if="props.cursos.length !== 0">
            <div class=" mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 mt-7 sm:p-8 bg-white shadow-2xl sm:rounded-lg">
                    <v-virtual-scroll
                        :items="props.cursos"
                        height="500"
                        item-height="400"
                        class="mt-4"

                    >
                        <template v-slot:default="{ item }">
<!--                            <v-list-item>-->
<!--                                <template v-slot:prepend>-->
<!--                                    <div class="d-flex align-center text-caption text-medium-emphasis me-1">-->
<!--                                        <template v-if="item.estado === 0">-->
<!--                                            <v-chip variant="flat" color="warning" prepend-icon="$info">-->
<!--                                                Curso por realizar-->
<!--                                            </v-chip>-->
<!--                                        </template>-->
<!--                                        <template v-else>-->
<!--                                            <v-chip variant="flat" color="success" prepend-icon="$info">-->
<!--                                                En curso-->
<!--                                            </v-chip>-->
<!--                                        </template>-->
<!--                                    </div>-->
<!--                                </template>-->

<!--                                <div class="ml-8">-->
<!--                                    <v-list-item-title class="text-h5">{{ item.nombreCurso }}</v-list-item-title>-->
<!--                                    <v-list-item-subtitle class="text-h6">-->
<!--                                        {{item.objetivoEvento}}-->
<!--                                    </v-list-item-subtitle>-->
<!--                                    <v-list-item-action class="mt-5">-->
<!--                                        <div v-if="item.docente_inscrito.some(inscrito => inscrito.id === props.auth.user.docente_id)">-->
<!--                                            <danger-button @click="desinscribirme(props.auth.user.docente_id, item.id)">Desinscribirme</danger-button>-->
<!--                                        </div>-->
<!--                                    </v-list-item-action>-->
<!--                                </div>-->
<!--                                <div class="d-flex justify-space-between px-4 pt-4">-->
<!--                                    <div class="d-flex align-center text-caption text-medium-emphasis me-1">-->

<!--                                    </div>-->
<!--                                    <div v-if="item.docente_inscrito.some(inscrito => inscrito.id === props.auth.user.docente_id)">-->
<!--                                                <v-alert variant="outlined" color="success">-->
<!--                                                    <strong class=""> Inscrito </strong>-->
<!--                                                </v-alert>-->

<!--                                    </div>-->

<!--                                    <div v-else>-->
<!--                                        <secondary-button type="submit" @click="submit(item.id)">Inscribirse</secondary-button>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </v-list-item>-->
<!--                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4"> &lt;!&ndash; Añadido gap-4 para el espacio entre columnas &ndash;&gt;-->
<!--                                <div class="flex justify-center items-center">-->
                                    <div class="max-w-2xl mx-auto overflow-hidden md:max-w-4xl">
                                        <div class="md:flex">
                                            <div class="md:flex items-center">
                                                <template v-if="item.estado === 0">
                                                    <v-chip variant="flat" color="warning" prepend-icon="$info">
                                                        Curso por realizar
                                                    </v-chip>
                                                </template>
                                                <template v-else-if="item.estado === 1">
                                                    <v-chip variant="flat" color="success" prepend-icon="$info">
                                                        En curso
                                                    </v-chip>
                                                </template>
                                                <template v-else>
                                                    <v-chip variant="flat" color="error" prepend-icon="$error">
                                                        Finalizado
                                                    </v-chip>
                                                </template>
                                            </div>
                                            <div class="p-8">
                                                <div class="uppercase tracking-wide text-sm font-semibold">{{ item.nombreCurso }}</div>
                                                <p class="block mt-1 text-sm leading-tight font-medium text-black hover:text-blue-400 hover:underline" @click="openDialog(item)">CLICK PARA CONOCER MAS DEL CURSO…</p>
                                                <p class="mt-2 text-black">Fecha de realizacion: {{ item.fecha_I.split('-').reverse().join('/') }} al {{ item.fecha_F.split('-').reverse().join('/') }}</p>
                                            </div>
                                            <div class="flex justify-end items-center">
<!--                                                <div class="p-8">-->
<!--                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">-->
<!--                                                        <div class="flex justify-center">-->
<!--                                                            <button class="rounded-lg bg-white shadow-2xl">-->
<!--                                                                <v-icon>-->
<!--                                                                    mdi-eye-arrow-right-outline-->
<!--                                                                </v-icon>-->
<!--                                                            </button>-->
<!--                                                        </div>-->
<!--                                                        <div class="flex justify-center">-->
<!--                                                            <div v-if="item.docente_inscrito.some(inscrito => inscrito.id === props.auth.user.docente_id)" class="w-[120px]">-->
<!--                                                                <v-alert variant="outlined" color="success">-->
<!--                                                                    <strong class=""> Inscrito </strong>-->
<!--                                                                </v-alert>-->
<!--                                                            </div>-->

<!--                                                            <div v-else>-->
<!--                                                                <secondary-button type="submit" @click="confirm_inscripcion(item.id)">Inscribirse</secondary-button>-->
<!--                                                            </div>-->
<!--                                                        </div>-->
<!--                                                    </div>-->
<!--                                                </div>-->
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<!--                                            <div class="flex justify-center">-->
<!--                                                <button class="rounded-lg bg-white shadow-2xl hover:bg-gray-500" @click="openDialog(item)">-->
<!--                                                    <v-icon>-->
<!--                                                        mdi-eye-arrow-right-outline-->
<!--                                                    </v-icon> Ver-->
<!--                                                </button>-->
<!--                                            </div>-->
                                            <div class="flex justify-center p-2">
                                                <div v-if="item.docente_inscrito.some(inscrito => inscrito.id === props.auth.user.docente_id)" class="w-[120px]">
                                                    <v-alert variant="outlined" color="success">
                                                        <strong class=""> Inscrito </strong>
                                                    </v-alert>
                                                </div>
<!--                                                Aqui lleva esta condicion item.estado !== 2-->
                                                <div v-else-if="item.estado !== 2">
                                                    <secondary-button type="submit" @click="confirm_inscripcion(item)">Inscribirse</secondary-button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
<!--                                </div>-->
<!--                            </div>-->
                        </template>
                    </v-virtual-scroll>
                </div>
            </div>
        </template>
        <template v-else>
            <div class="mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 mt-7 sm:p-8 bg-white shadow sm:rounded-lg">
                    <v-alert
                    border="start"
                    color="info"
                    type="info"
                    title="Cursos"
                    >
                        <strong>Actualmente no hay cursos que visualizar !Pronto deberian ser agregados!</strong>
                    </v-alert>
                </div>
            </div>
        </template>
        <CustomSnackBar v-model="snackbar" :message="message" :color="color" @update:modelValue="snackbar = $event">

        </CustomSnackBar>

        <InfoDialog v-model="dialogInfo" :curso="curso_selected" @update:modelValue="dialogInfo = $event"></InfoDialog>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
