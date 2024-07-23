<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {computed, onMounted, ref, watch} from "vue";
import {Head, router} from "@inertiajs/vue3";
import TablaMisCursoDocente from "@/Pages/Views/cursos/tablas/TablaMisCursoDocente.vue";
import NavLink from "@/Components/NavLink.vue";
import CustomSnackBar from "@/Components/CustomSnackBar.vue";
import InfoDialog from "@/Components/InfoDialog.vue";
import {Curso} from "@/store/curso.js";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const store = Curso()
const props = defineProps({
    docente: Object,
    auth: Object,
    misCursos: Object
});
// console.log(props.misCursos)
const curso_selected = ref({})
const dialogInfo = ref(false)
const search = ref()
const color = ref("")
const message = ref("")
const snackbar = ref(false)
const timeout = ref(0)
const id_ref = ref(null)
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

const parseArray = computed(() => {
     return Object.values(props.misCursos);
})
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
    window.Echo.private('calificacion-update').listen('CalificacionEvent', (event) => {
        snackEventActivator()
    })
    // console.log(parseArray.value)
    // id_ref.value !== null ? store.get_curso_info(id_ref.value.id) : 0
});

const reloadPage = () => {
    router.reload();
    snackbar.value = false
}

function openDialog(curso){
    // console.log(typeof curso)
    curso_selected.value = curso
    dialogInfo.value = true
}
watch(() => curso_selected.value, (newID) => {
    // id_ref.value = newID
    //
    // store.infoCourse(id_ref.value.id)
});
</script>

<template>
    <Head title="Mis Cursos"/>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-medium text-gray-900">Mis Cursos</h2>
        </template>

        <template v-if="props.misCursos.length !== 0">
            <div class=" mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 mt-7 sm:p-8 bg-white shadow-2xl sm:rounded-lg">
<!--                    <v-virtual-scroll-->
<!--                        :items="props.misCursos"-->
<!--                        height="500"-->
<!--                        item-height="400"-->
<!--                        class="mt-4"-->

<!--                    >-->
<!--                        <template v-slot:default="{ item }">-->
<!--                            &lt;!&ndash;                            <v-list-item>&ndash;&gt;-->
<!--                            &lt;!&ndash;                                <template v-slot:prepend>&ndash;&gt;-->
<!--                            &lt;!&ndash;                                    <div class="d-flex align-center text-caption text-medium-emphasis me-1">&ndash;&gt;-->
<!--                            &lt;!&ndash;                                        <template v-if="item.estado === 0">&ndash;&gt;-->
<!--                            &lt;!&ndash;                                            <v-chip variant="flat" color="warning" prepend-icon="$info">&ndash;&gt;-->
<!--                            &lt;!&ndash;                                                Curso por realizar&ndash;&gt;-->
<!--                            &lt;!&ndash;                                            </v-chip>&ndash;&gt;-->
<!--                            &lt;!&ndash;                                        </template>&ndash;&gt;-->
<!--                            &lt;!&ndash;                                        <template v-else>&ndash;&gt;-->
<!--                            &lt;!&ndash;                                            <v-chip variant="flat" color="success" prepend-icon="$info">&ndash;&gt;-->
<!--                            &lt;!&ndash;                                                En curso&ndash;&gt;-->
<!--                            &lt;!&ndash;                                            </v-chip>&ndash;&gt;-->
<!--                            &lt;!&ndash;                                        </template>&ndash;&gt;-->
<!--                            &lt;!&ndash;                                    </div>&ndash;&gt;-->
<!--                            &lt;!&ndash;                                </template>&ndash;&gt;-->

<!--                            &lt;!&ndash;                                <div class="ml-8">&ndash;&gt;-->
<!--                            &lt;!&ndash;                                    <v-list-item-title class="text-h5">{{ item.nombreCurso }}</v-list-item-title>&ndash;&gt;-->
<!--                            &lt;!&ndash;                                    <v-list-item-subtitle class="text-h6">&ndash;&gt;-->
<!--                            &lt;!&ndash;                                        {{item.objetivoEvento}}&ndash;&gt;-->
<!--                            &lt;!&ndash;                                    </v-list-item-subtitle>&ndash;&gt;-->
<!--                            &lt;!&ndash;                                    <v-list-item-action class="mt-5">&ndash;&gt;-->
<!--                            &lt;!&ndash;                                        <div v-if="item.docente_inscrito.some(inscrito => inscrito.id === props.auth.user.docente_id)">&ndash;&gt;-->
<!--                            &lt;!&ndash;                                            <danger-button @click="desinscribirme(props.auth.user.docente_id, item.id)">Desinscribirme</danger-button>&ndash;&gt;-->
<!--                            &lt;!&ndash;                                        </div>&ndash;&gt;-->
<!--                            &lt;!&ndash;                                    </v-list-item-action>&ndash;&gt;-->
<!--                            &lt;!&ndash;                                </div>&ndash;&gt;-->
<!--                            &lt;!&ndash;                                <div class="d-flex justify-space-between px-4 pt-4">&ndash;&gt;-->
<!--                            &lt;!&ndash;                                    <div class="d-flex align-center text-caption text-medium-emphasis me-1">&ndash;&gt;-->

<!--                            &lt;!&ndash;                                    </div>&ndash;&gt;-->
<!--                            &lt;!&ndash;                                    <div v-if="item.docente_inscrito.some(inscrito => inscrito.id === props.auth.user.docente_id)">&ndash;&gt;-->
<!--                            &lt;!&ndash;                                                <v-alert variant="outlined" color="success">&ndash;&gt;-->
<!--                            &lt;!&ndash;                                                    <strong class=""> Inscrito </strong>&ndash;&gt;-->
<!--                            &lt;!&ndash;                                                </v-alert>&ndash;&gt;-->

<!--                            &lt;!&ndash;                                    </div>&ndash;&gt;-->

<!--                            &lt;!&ndash;                                    <div v-else>&ndash;&gt;-->
<!--                            &lt;!&ndash;                                        <secondary-button type="submit" @click="submit(item.id)">Inscribirse</secondary-button>&ndash;&gt;-->
<!--                            &lt;!&ndash;                                    </div>&ndash;&gt;-->
<!--                            &lt;!&ndash;                                </div>&ndash;&gt;-->
<!--                            &lt;!&ndash;                            </v-list-item>&ndash;&gt;-->
<!--                            &lt;!&ndash;                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4"> &lt;!&ndash; Añadido gap-4 para el espacio entre columnas &ndash;&gt;&ndash;&gt;-->
<!--                            &lt;!&ndash;                                <div class="flex justify-center items-center">&ndash;&gt;-->
<!--                            <div class="max-w-2xl mx-auto overflow-hidden md:max-w-4xl">-->
<!--                                <div class="md:flex">-->
<!--                                    <div class="md:flex items-center">-->
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
<!--                                    <div class="p-8">-->
<!--                                        <div class="uppercase tracking-wide text-sm font-semibold">{{ item.nombreCurso }}</div>-->
<!--                                        <p class="block mt-1 text-sm leading-tight font-medium text-black hover:underline">{{ item.objetivoEvento }}</p>-->
<!--                                        <p class="mt-2 text-black">Fecha de realizacion: {{ item.fecha_I.split('-').reverse().join('/') }} al {{ item.fecha_F.split('-').reverse().join('/') }}</p>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">-->
<!--                                    <div class="flex justify-center">-->
<!--                                        <button class="rounded-lg bg-white shadow-2xl hover:bg-gray-500" @click="openDialog(item.id)">-->
<!--                                            <v-icon>-->
<!--                                                mdi-eye-arrow-right-outline-->
<!--                                            </v-icon> Ver-->
<!--                                        </button>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </div>-->
                            <!--                                </div>-->
                            <!--                            </div>-->
<!--                        </template>-->
<!--                    </v-virtual-scroll>-->
                    <div class="grid grid-cols-1 md:grid-cols-3">
                        <div v-for="(item, index) in parseArray" :key="index" class="flex justify-center items-center">
                            <div class="max-w-2xl mx-auto overflow-hidden md:max-w-4xl">
                                <div class="md:flex">
                                    <div class="md:flex items-center">
                                        <template v-if="item.estado === 0">
                                            <v-chip variant="flat" color="warning" prepend-icon="$info">
                                                Curso por realizar
                                            </v-chip>
                                        </template>
                                        <template v-else>
                                            <v-chip variant="flat" color="success" prepend-icon="$info">
                                                En curso
                                            </v-chip>
                                        </template>
                                    </div>
                                    <div class="p-8">
                                        <div class="uppercase tracking-wide text-sm font-semibold">{{ item.nombreCurso }}</div>
                                        <p class="block mt-1 text-sm leading-tight font-medium text-black hover:text-blue-400 hover:underline" @click="openDialog(item)">CLICK PARA CONOCER MAS DEL CURSO…</p>
                                        <p class="mt-2 text-black">Fecha de realizacion: {{item.fecha_I}}</p>
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<!--                                        <div class="flex justify-end md:justify-end">-->
<!--                                            <button class="rounded-lg bg-white shadow-2xl hover:bg-gray-500" @click="openDialog(item)">-->
<!--                                                <v-icon>-->
<!--                                                    mdi-eye-arrow-right-outline-->
<!--                                                </v-icon> Ver-->
<!--                                            </button>-->
<!--                                        </div>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
        <div class="mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 mt-7 sm:p-8 bg-white shadow sm:rounded-lg">
                <v-col>
                    <NavLink :href="route('d.c.r')">
                        <v-btn width="600" height="50" color="blue-darken-1" prepend-icon="mdi-archive">Ver todos los cursos finalizados</v-btn>
                    </NavLink>
                </v-col>
            </div>
        </div>
        <CustomSnackBar :message="message" :timeout="timeout" :color="color" v-model="snackbar" @update:modelValue="snackbar = $event">
            <template v-slot:reloadingbutton>
                <div class="flex justify-start pa-1">
                    <v-btn @click="reloadPage" icon="mdi-reload"></v-btn>
                </div>
            </template>
        </CustomSnackBar>
        <InfoDialog v-model="dialogInfo" :curso="curso_selected" @update:modelValue="dialogInfo = $event"></InfoDialog>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
