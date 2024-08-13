<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {computed, onMounted} from "vue";
import {ref} from 'vue';
import NavLink from "@/Components/NavLink.vue";
import {FODAPStore} from "@/store/server.js";
import {Curso} from "@/store/curso.js";
import InputLabel from "@/Components/InputLabel.vue";
import {router, useForm} from "@inertiajs/vue3";
import DangerButton from "@/Components/DangerButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CustomSnackBar from "@/Components/CustomSnackBar.vue";
import Calificaciones from "@/Components/Calificaciones.vue";
import CalificacionesUpdate from "@/Components/CalificacionesUpdate.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Loading from "@/Components/Loading.vue";
import {AlertLoading, errorMsg, notify, success_alert} from "@/jsfiels/alertas.js";
import Swal from "sweetalert2";

const my_curso_store = Curso()
const store = FODAPStore()

const props = defineProps({
    curso: Object,
    auth: Object,
    facilitador: Object,
    inscritos: Array,
    errors: Object
});
const timeout = ref();
const id_teacher = ref(null);
const snackbar = ref(false);
const dialog = ref(false);
const loading = ref(false);
const message = ref("")
const color = ref("")
const dialogCalificacion = ref(false);
const curso_id = ref()
const docente = ref()
const dialogCalificacionUpdate = ref(false)
const calificacion = ref()
const show_ficha = ref(false)
const docente_calificacion = (docente_id) => {
    docente.value = docente_id
    curso_id.value = props.curso.id
    dialogCalificacion.value = true
}
const update_docente_calificacion = (docente_id, calification) => {
    docente.value = docente_id
    curso_id.value = props.curso.id
    calificacion.value = calification
    dialogCalificacionUpdate.value = true
}

const addCalificacion = (form) => {
    form.post(route('calificaciones.facilitador.create'), {
        onSuccess: () => {
            dialogCalificacion.value = false
            success_alert('Exito.', 'Calificación asignada.')
        },
        onError: () => {
            dialogCalificacion.value = false
            notify('Alerta','warning', `${format_errors(props.errors)}`)
            message.value = ""
        }
    })
}
const updateCalificacion = (form) => {
    form.put(route('calificaciones.facilitador.update'), {
        onSuccess: () => {
            dialogCalificacionUpdate.value = false
            success_alert('Exito.', 'Calificación actualizada.')
        },
        onError: () => {
            dialogCalificacionUpdate.value = false
            notify('Alerta','warning', `${format_errors(props.errors)}`)
            message.value = ""
        }
    })
}
const closeModal = () => {
    show_ficha.value = false;
};

const submit = (inscripcion, id) => {
    axios.get(route('cdi.pdf'), {
        params: {
            docente: inscripcion,
            id_curso: id
        }
    }).then(res => {
        const url = '/storage/CDI.pdf';
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'CDI.pdf');
        document.body.appendChild(link);
        link.click();
        // snackSuccessActivator()
    }).catch(error => {
        // snackErrorActivator()
    })
}

const generar_ficha = () => {
    loading.value = true

            // AlertLoading('Generando documento...', 'Esta acción puede tardar unos minutos')
            if (props.curso.ficha_tecnica){
                axios.get(route('pdf.ficha.tecnica'), {
                    params: {
                        id_ficha: props.curso.ficha_tecnica.id
                    }
                }).then(res => {
                    loading.value = false
                    const url = '/storage/ficha.pdf';
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'ficha.pdf');
                    document.body.appendChild(link);
                    link.click();
                    // snackSuccessActivator()
                    success_alert('Exito', `${res.response.data}`)
                }).catch(error => {
                    loading.value = false
                    // snackErrorActivator()
                    errorMsg('Ateción.', `${error.response.data.errors}`)
                })
            }else{
                loading.value = false
                notify('Atención', 'warning', 'El PDF no se puede generar debido a que no se ha capturado la ficha tecnica del curso.')
            }

};

const form = useForm({
    calificacion: null,
    docente_id: null,
    curso_id: props.curso.id
})
const fila_seleccionada = (id) => {
    form.docente_id = id
    dialog.value = true
}


const format_errors = (errors) => {
    for (const errorsKey in errors) {
        message.value += errors[errorsKey]
    }
    return message.value.split('.').join('. ');
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
    // console.log(props.curso.ficha_tecnica.id)
    window.Echo.private('inscritos-chanel').listen('InscripcionEvent', (event) => {
        // my_curso_store.update_inscritos(event.inscritos)
        // snackEventActivator()
    })
    window.Echo.private('calificacion-update').listen('CalificacionEvent', (event) => {
        // my_curso_store.update_calificacion(event.calificacion[0])
        // snackEventActivator()
    })
    my_curso_store.inscritos_curso(props.curso.id)
    if (props.curso?.ficha_tecnica) {
        color.value = "success";
    } else {
        color.value = "error";
    }
});

const inform = () => {
    notify('Ateción', 'info', 'El curso no cuenta con ficha técnica.')
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="grid grid-rows-2">
                <div class="flex justify-center">
                    <h2 class="text-lg font-medium text-gray-900">{{curso.nombreCurso}}</h2>
                </div>
                <div class="flex justify-center">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:w-[900px]">
                        <div class="flex justify-start">
                            <NavLink :href="route('show.facilitadores', props.facilitador.id)" as="button">
                                <v-btn icon="mdi-arrow-left">

                                </v-btn>
                            </NavLink>
                        </div>
                        <div class="flex justify-center items-center ma-3">
                            <v-tooltip location="right">
                                <template v-slot:activator="{ props }">
                                    <v-btn
                                        icon="mdi-file"
                                        :color="color"
                                        v-bind="props"
                                        size="large"
                                    >
                                    </v-btn>
                                </template>
                                <template v-if="color === 'success'">
                                    <span>Este curso cuenta con su ficha técnica.</span>
                                </template>
                                <template v-if="color === 'error'">
                            <span
                            >Este curso no cuenta con su ficha
                                técnica.</span
                            >
                                </template>
                            </v-tooltip>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <div class=" mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 mt-7 sm:p-8 bg-white shadow sm:rounded-lg">
                <v-table fixed-header height="500px" hover>
                    <thead>
                    <tr>
                        <th class="text-center">Nombre</th>
                        <th class="text-center">Apellido Paterno</th>
                        <th class="text-center">Apellido Materno</th>
                        <th class="text-center">CALIFICACIÓN</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr
                        v-for="inscrito in props.inscritos"
                        :key="inscrito.id" :class="{ id_teacher: inscrito.id === id_teacher }"

                    >
                        <td class="text-center">{{inscrito.nombre}}</td>
                        <td class="text-center">{{inscrito.apellidoPat}}</td>
                        <td class="text-center">{{inscrito.apellidoMat}}</td>
                        <td class="text-center">
                            <template v-if="props.curso.aceptado === 1">
                                <template v-if="inscrito.calificacion === null">
                                    <v-btn icon @click="docente_calificacion(inscrito.id)">
                                        <v-icon>mdi-pencil-plus</v-icon>
                                    </v-btn>
                                </template>
                                <template v-else-if="inscrito.calificacion === 0">
                                    <v-chip
                                        class="ma-2"
                                        color="red"
                                        text-color="white"
                                        @click="update_docente_calificacion(inscrito.id, inscrito.calificacion)"
                                    >
                                        <p class="text-center">NO APROBADO</p>
                                    </v-chip>
                                </template>
                                <template v-else-if="inscrito.calificacion === 1">
                                    <v-chip
                                        class="ma-2"
                                        color="success"
                                        text-color="white"
                                        @click="update_docente_calificacion(inscrito.id, inscrito.calificacion)"
                                    >
                                        <p class="text-center">APROBADO</p>
                                    </v-chip>
                                </template>
                            </template>
                        </td>
                    </tr>
                    </tbody>
                </v-table>
            </div>
        </div>
        <div class=" mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 mt-7 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="grid grid-rows-1">
                    <div class="flex justify-center">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="flex justify-center items-center">
                                <v-tooltip location="left" class="">
                                    <template v-slot:activator="{ props }">
                                        <v-btn icon v-bind="props" color="blue-darken-1" size="normal">
                                            <v-icon>
                                                mdi-help
                                            </v-icon>
                                        </v-btn>
                                    </template>
                                    <span>Indicar el periodo en el que se requiere la formación docente o
                                                        actualización profesiona (enero-junio o agosto-diciembre)</span>
                                </v-tooltip>
                                <NavLink :href="route('crear.ficha', [props.auth.user.docente_id, props.curso.id])">
                                    <v-btn color="blue-darken-1">
                                        Crear ficha técnica
                                    </v-btn>
                                </NavLink>
                            </div>
                            <div class="flex justify-center items-center">
                                <!--                            :href="route('edit.ficha', [props.auth.user.id, props.curso.id])"-->
                                <v-btn color="blue-darken-1" @click="show_ficha = true">
                                    Ver ficha técnica
                                </v-btn>
                                <Modal :show="show_ficha" @close="closeModal">
                                    <template v-if="props.curso.ficha_tecnica">
                                        <div class="grid grid-rows-3">
                                            <div class="flex justify-center items-center pa-4">
                                                {{props.curso.ficha_tecnica.competencias_desarrollar}}
                                            </div>
                                            <div class="flex justify-center items-center pa-4">
                                                {{props.curso.ficha_tecnica.objetivo_general}}
                                            </div>
                                            <div class="flex justify-end items-center pa-4">
                                                <SecondaryButton @click="show_ficha = false">Cerrar</SecondaryButton>
                                            </div>
                                        </div>
                                    </template>
                                    <template v-else-if="props.curso.ficha_tecnica === null">
                                        <v-alert
                                            density="compact"
                                            type="warning"
                                            title="Alerta"
                                            text="Este curso no posee una ficha tecnica"
                                        ></v-alert>
                                    </template>
                                </Modal>
                            </div>
                            <div class="flex justify-center items-center">
                                <!--                            :href="route('edit.ficha', [props.auth.user.id, props.curso.id])"-->
                                <template v-if="props.auth?.user?.docente_id && props.curso?.id && props.curso.ficha_tecnica">
                                    <NavLink
                                        :href="route('edit.ficha', [
                                        props.auth.user.docente_id,
                                        props.curso.id,
                            ])"
                                    >
                                        <v-btn color="blue-darken-1">
                                            Editar ficha técnica
                                        </v-btn>
                                    </NavLink>
                                </template>
                                <template v-else>
                                    <v-btn color="blue-darken-1" @click="errorMsg('¡Atención!', `El id del docente que tiene iniciada la sesión: ${props.auth?.user?.docente_id}, el curso relacionado con el mismo con ID: ${props.curso?.id} y el ID de la ficha técnica relacionada con el curso: ${props.curso?.ficha_tecnica?.id}, estos valores se requieren para editar la ficha técnica. Si falta un ID no mostrara el formulario, pero si los primeros dos valores están presentes significa que no existe o no ha sido capturada la ficha técnica.`)">
                                        Editar ficha técnica
                                    </v-btn>
                                </template>
                            </div>
                            <div class="flex justify-center items-center">
                                <v-btn @click="generar_ficha" color="blue-darken-1" prepend-icon="mdi-file-pdf-box">
                                    Descargar PDF
                                </v-btn>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class=" mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 mt-7 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex items-center">
                    <div>
                        <div class="p-5">
                            <div class="flow-root ...">
                                <strong>Asignaturas en la que se requiere formación o actualización: </strong>
                                <v-divider></v-divider>
                                <span>{{props.curso.asignaturaFA}}</span>
                            </div>
                            <div class="flow-root ... pt-7">
                                <strong>Contenidos temáticos en que se requiere la formación o actualización: </strong>
                                <v-divider></v-divider>
                                <span>{{props.curso.contenidosTM}}</span>
                            </div>
                            <div class="flow-root ... pt-7">
                                <strong>Número de profesores(as) que la requieren: </strong>
                                <span>{{props.curso.numeroProfesores}}</span>
                            </div>
                            <div class="flow-root ... pt-5">
                                <strong>Modalidad: </strong>
                                <template v-if="props.curso.modalidad === 1">
                                    <span>Virtual</span>
                                </template>
                                <template v-if="props.curso.modalidad === 2">
                                    <span>Presencial</span>
                                </template>
                                <template v-if="props.curso.modalidad === 3">
                                    <span>Hibrído</span>
                                </template>

                            </div>
                            <div class="flow-root ... pt-5">
                                <strong>Periodo en el que se requiere la formación o actualización (enero-junio o agosto diciembre): </strong>
                                <template v-if="props.curso.periodo === 1">
                                    <span>ENERO-JUNIO</span>
                                </template>
                                <template v-if="props.curso.periodo === 2  ">
                                    <span>AGOSTO-DICIEMBRE</span>
                                </template>
                            </div>
                            <div class="flow-root ... pt-5">
                                <strong>Carrera a la que va dirigido: </strong>
                                <span>{{props.curso.carrera.nameCarrera}}</span>
                            </div>
                            <div class="flow-root ... pt-5">
                                <strong>Facilitador(es): </strong>
                                <div v-for="facilitador in props.curso.deteccion_facilitador">
                                    <span>{{facilitador.nombre}}  {{facilitador.apellidoPat}}  {{facilitador.apellidoMat}}</span>
                                </div>
                            </div>
                            <template v-if="props.curso.facilitador_externo != null">
                                <div class="flow-root ... pt-5">
                                    <strong>Facilitador externo: </strong>
                                    <span>{{props.curso.facilitador_externo}}</span>
                                </div>
                            </template>
                            <div class="flow-root ... pt-5">
                                <strong>Tipo de curso, taller, conferencias, etc: </strong>
                                <template v-if="props.curso.tipo_actividad === 1">
                                    <span>TALLER</span>
                                </template>
                                <template v-if="props.curso.tipo_actividad === 2">
                                    <span>CURSO</span>
                                </template>
                                <template v-if="props.curso.tipo_actividad === 3">
                                    <span>CURSO-TALLER</span>
                                </template>
                                <template v-if="props.curso.tipo_actividad === 4">
                                    <span>FORO</span>
                                </template>
                                <template v-if="props.curso.tipo_actividad === 5">
                                    <span>SEMINARIO</span>
                                </template>
                                <template v-if="props.curso.tipo_actividad === 6">
                                    <span>DIPLOMADO</span>
                                </template>
                            </div>
                            <div class="flow-root ... pt-5">
                                <strong>Tipo de solicitud: </strong>
                                <template v-if="props.curso.tipo_FDoAP === 1">
                                    <span>FORMACIÓN DOCENTE</span>
                                </template>
                                <template v-if="props.curso.tipo_FDoAP === 2">
                                    <span>ACTUALIZACIÓN PROFESIONAL</span>
                                </template>
                            </div>
                            <div class="flow-root ... pt-5">
                                <strong>Fechas en las que se realizara la actividad o evento: </strong>
                                <span>Del {{props.curso.fecha_I}} al {{props.curso.fecha_F}}</span>
                            </div>
                            <div class="flow-root ... pt-5">
                                <strong>Horarios en las que se realizara la actividad o evento: </strong>
                                <span>De {{props.curso.hora_I}} a {{props.curso.hora_F}}</span>
                            </div>
                            <div class="flow-root ... pt-5">
                                <strong>Objetivo de la actividad o evento: </strong>
                                <v-divider></v-divider>
                                <span>{{props.curso.objetivoEvento}}</span>
                            </div>
                            <template v-if="props.curso.obs === 1">
                                <div class="flow-root ... pt-5">
                                    <strong>Observaciones: </strong>
                                    <v-divider></v-divider>
                                    <span>{{props.curso.observaciones}}</span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<!--        <div class=" mx-auto sm:px-6 lg:px-8 space-y-6">-->
<!--            <div class="p-4 mt-7 sm:p-8 bg-white shadow sm:rounded-lg">-->
<!--                    <v-card elevation="0">-->
<!--                        <v-card-title class="text-center">Ficha Técnica</v-card-title>-->
<!--                        <v-row justify="center">-->
<!--                            <v-col cols="6" align="center">-->
<!--                                <NavLink :href="route('crear.ficha', [props.facilitador.id, props.curso.id])">-->
<!--                                    <v-btn color="blue-darken-1">-->
<!--                                        Crear ficha técnica-->
<!--                                    </v-btn>-->
<!--                                </NavLink>-->
<!--                            </v-col>-->
<!--                            <v-col cols="6" align="center" class="mt-2">-->
<!--                                <template v-if="props.ficha_tecnica === null">-->
<!--                                    <v-btn color="blue-darken-1" prepend-icon="mdi-file-pdf-box" @click="snackErrorActivator">-->
<!--                                        Descargar PDF-->
<!--                                    </v-btn>-->
<!--                                </template>-->
<!--                                <template v-else>-->
<!--                                    <v-btn color="blue-darken-1" prepend-icon="mdi-file-pdf-box" @click="generar_ficha">-->
<!--                                        Descargar PDF-->
<!--                                    </v-btn>-->
<!--                                </template>-->
<!--                            </v-col>-->
<!--                        </v-row>-->
<!--                    </v-card>-->
<!--            </div>-->
<!--        </div>-->
<!--        <CustomSnackBar :message="message" :timeout="timeout" :color="color" v-model="snackbar" @update:modelValue="snackbar = $event">-->

<!--        </CustomSnackBar>-->
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
        <Calificaciones v-model="dialogCalificacion" :curso="curso_id" :docente="docente" @form:Calificacion="addCalificacion" @update:modelValue="dialogCalificacion = $event"></Calificaciones>

        <CalificacionesUpdate v-model="dialogCalificacionUpdate" :calificacion="calificacion" :curso="curso_id" :docente="docente" @update:Calificacion="updateCalificacion" @update:modelValue="dialogCalificacionUpdate = $event"></CalificacionesUpdate>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
