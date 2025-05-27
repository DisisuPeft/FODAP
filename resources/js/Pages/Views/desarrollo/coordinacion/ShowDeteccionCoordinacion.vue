<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TablaDetecciones from "@/Pages/Views/desarrollo/tablas/TablaDetecciones.vue";
import { computed, onMounted, ref } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm, Head, router } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import NavLink from "@/Components/NavLink.vue";
import DangerButton from "@/Components/DangerButton.vue";
import EliminarDeteccionConfirmation from "@/Pages/Views/dialogs/EliminarDeteccionConfirmation.vue";
import { AlertLoading, errorMsg, success_alert } from "@/jsfiels/alertas.js";
import Swal from "sweetalert2";
import TextAreaInput from "@/Components/TextAreaInput.vue";

const props = defineProps({
    deteccion: Object,
    auth: Object,
});
// console.log(props.deteccion.tipo_FDoAP === 1);
const dialog = ref(false);
const message = ref("");
// const formatFechaF = computed(() => {
//     return new Date(props.deteccion.fecha_F).toLocaleDateString('es-MX');
// })
// // Computed propierties
//
//
// const formatFechaI = computed(() => {
//     return new Date(props.deteccion.fecha_I).toLocaleDateString('es-MX');
// });

const form = useForm({
    observaciones: "",
});

const formAceptado = useForm({
    aceptado: 1,
});

function aceptar() {
    Swal.fire({
        title: "¿Seguro desea aceptar este curso?",
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
                "Guardando curso...",
                "Esta accion puede tardar unos minutos"
            );
            formAceptado.post(route("store.aceptado", props.deteccion.id), {
                onSuccess: () => {
                    success_alert("Exito", "El curso se acepto con exito.");
                },
                onError: () => {
                    // console.log(props.errors)
                    errorMsg("Error", `${format_errors(props.errors)}`);
                    message.value = "";
                },
            });
        }
    });
}
const format_errors = (errors) => {
    for (const errorsKey in errors) {
        message.value += errors[errorsKey];
    }
    return message.value.split(".").join(". ");
};

const formC = useForm({
    correccion: "",
});
function addCorrecciones() {
    Swal.fire({
        title: "¿Seguro desea agregar estas correcciones?",
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
                "Guardando curso...",
                "Esta accion puede tardar unos minutos"
            );
            if (!props.deteccion?.correccion) {
                formC.post(route("add.correccion", props.deteccion.id), {
                    onSuccess: () => {
                        success_alert("Exito", "Se han añadido correcciones.");
                    },
                    onError: () => {
                        // console.log(props.errors)
                        errorMsg("Error", `${format_errors(props.errors)}`);
                        message.value = "";
                    },
                });
            } else {
                formC.put(route("update.correccion", props.deteccion.id), {
                    onSuccess: () => {
                        success_alert(
                            "Exito",
                            "Se han actualizado las correcciones."
                        );
                    },
                    onError: () => {
                        // console.log(props.errors)
                        errorMsg("Error", `${format_errors(props.errors)}`);
                        message.value = "";
                    },
                });
            }
        }
    });
}

function obs() {
    Swal.fire({
        title: "¿Seguro desea agregar esta observacion?",
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
                "Guardando curso...",
                "Esta accion puede tardar unos minutos"
            );
            form.put(route("update.observaciones", props.deteccion.id), {
                onSuccess: () => {
                    success_alert("Exito", "Se han añadido observaciones.");
                },
                onError: () => {
                    // console.log(props.errors)
                    errorMsg("Error", `${format_errors(props.errors)}`);
                    message.value = "";
                },
            });
        }
    });
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

    if (!props.deteccion?.correccion) {
        return formC.correccion;
    } else {
        formC.correccion = props.deteccion?.correccion.observaciones;
    }
});
</script>

<template>
    <template v-if="props.deteccion === null">
        <AuthenticatedLayout>
            <template #header>
                <h2
                    class="font-semibold text-xl text-gray-800 leading-tight"
                ></h2>
                <div class="flex justify-start align-center mt-3 ml-5">
                    <NavLink
                        :href="route('index.detecciones')"
                        as="button"
                        type="button"
                    >
                        <v-btn
                            color="blue-darken-1"
                            icon="mdi-arrow-left"
                        ></v-btn>
                    </NavLink>
                </div>
            </template>
            <v-alert icon="$info" color="info">
                <strong>Probablemente no existe este registo</strong>
            </v-alert>
        </AuthenticatedLayout>
    </template>
    <template v-else>
        <AuthenticatedLayout>
            <template #header>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ props.deteccion.nombreCurso }}
                </h2>
                <div class="flex justify-start align-center mt-3 ml-5">
                    <NavLink
                        :href="route('index.detecciones')"
                        as="button"
                        type="button"
                    >
                        <v-btn
                            color="blue-darken-1"
                            icon="mdi-arrow-left"
                        ></v-btn>
                    </NavLink>
                </div>
                <template v-if="props.deteccion.aceptado === 0">
                    <div class="grid grid-cols-2">
                        <div class="flex justify-center">
                            <form @submit.prevent="aceptar">
                                <PrimaryButton class="mt-5"
                                    >Aceptar curso</PrimaryButton
                                >
                            </form>
                        </div>
                        <div class="flex justify-start">
                            <DangerButton @click="dialog = true" class="mt-5"
                                >Eliminar curso</DangerButton
                            >
                            <EliminarDeteccionConfirmation
                                v-model="dialog"
                                :curso="props.deteccion.id"
                                @update:modelValue="dialog = $event"
                            ></EliminarDeteccionConfirmation>
                        </div>
                    </div>
                </template>
                <template v-if="props.deteccion.aceptado === 1">
                    <div class="flex justify-start align-center mt-3 ml-5">
                        <NavLink
                            :href="route('curso.editar', props.deteccion.id)"
                        >
                            <primary-button>Editar</primary-button>
                        </NavLink>
                    </div>
                </template>
            </template>

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <div class="flex items-center">
                            <div>
                                <div class="p-5">
                                    <div class="flow-root ...">
                                        <template
                                            v-if="
                                                props?.deteccion?.tipo_FDoAP ===
                                                1
                                            "
                                        >
                                            <strong
                                                >Dimensión(es) en la(s) que se
                                                requiere Formación Docente
                                            </strong>
                                        </template>
                                        <template
                                            v-else-if="
                                                props?.deteccion?.tipo_FDoAP ===
                                                2
                                            "
                                        >
                                            <strong
                                                >Asignatura(s) en la(s) que se
                                                requiere Actualización
                                                profesional
                                            </strong>
                                        </template>
                                        <v-divider></v-divider>
                                        <span>{{
                                            props.deteccion.asignaturaFA
                                        }}</span>
                                    </div>
                                    <div class="flow-root ... pt-7">
                                        <template
                                            v-if="
                                                props?.deteccion?.tipo_FDoAP ===
                                                1
                                            "
                                        >
                                            <strong
                                                >Competencia(s) en la(s) que se
                                                requiere la Formación Docente
                                            </strong>
                                        </template>
                                        <template
                                            v-else-if="
                                                props?.deteccion?.tipo_FDoAP ===
                                                2
                                            "
                                        >
                                            <strong
                                                >Contenidos temáticos en que se
                                                requiere Actualización
                                                Profesional
                                            </strong>
                                        </template>
                                        <v-divider></v-divider>
                                        <span>{{
                                            props.deteccion.contenidosTM
                                        }}</span>
                                    </div>
                                    <div class="flow-root ... pt-7">
                                        <strong class="text-xl"
                                            >Número de profesores(as) que la
                                            requieren:
                                        </strong>
                                        <span>{{
                                            props.deteccion.numeroProfesores
                                        }}</span>
                                    </div>
                                    <div class="flow-root ... pt-5">
                                        <strong class="text-xl"
                                            >Modalidad:
                                        </strong>
                                        <template
                                            v-if="
                                                props.deteccion.modalidad === 1
                                            "
                                        >
                                            <span>Virtual</span>
                                        </template>
                                        <template
                                            v-if="
                                                props.deteccion.modalidad === 2
                                            "
                                        >
                                            <span>Presencial</span>
                                        </template>
                                        <template
                                            v-if="
                                                props.deteccion.modalidad === 3
                                            "
                                        >
                                            <span>Hibrído</span>
                                        </template>
                                    </div>
                                    <div class="flow-root ... pt-5">
                                        <strong class="text-xl"
                                            >Periodo en el que se requiere la
                                            formación o actualización
                                            (enero-junio o agosto diciembre):
                                        </strong>
                                        <template
                                            v-if="props.deteccion.periodo === 1"
                                        >
                                            <span>enero-junio</span>
                                        </template>
                                        <template
                                            v-if="props.deteccion.periodo === 2"
                                        >
                                            <span>agosto-diciembre</span>
                                        </template>
                                    </div>
                                    <div class="flow-root ... pt-5">
                                        <strong class="text-xl"
                                            >Carrera a la que va dirigido:
                                        </strong>
                                        <span>{{
                                            props.deteccion.carrera.nameCarrera
                                        }}</span>
                                    </div>
                                    <div class="flow-root ... pt-5">
                                        <strong class="text-xl"
                                            >Facilitador(es):
                                        </strong>
                                        <div
                                            v-for="facilitador in props
                                                .deteccion
                                                .deteccion_facilitador"
                                        >
                                            <span
                                                >{{ facilitador.nombre }}
                                                {{ facilitador.apellidoPat }}
                                                {{
                                                    facilitador.apellidoMat
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                    <template
                                        v-if="
                                            props.deteccion
                                                .facilitador_externo != null
                                        "
                                    >
                                        <div class="flow-root ... pt-5">
                                            <strong class="text-xl"
                                                >Facilitador externo:
                                            </strong>
                                            <span>{{
                                                props.deteccion
                                                    .facilitador_externo
                                            }}</span>
                                        </div>
                                    </template>
                                    <div class="flow-root ... pt-5">
                                        <strong class="text-xl"
                                            >Tipo de curso, taller,
                                            conferencias, etc:
                                        </strong>
                                        <template
                                            v-if="
                                                props.deteccion
                                                    .tipo_actividad === 1
                                            "
                                        >
                                            <span>TALLER</span>
                                        </template>
                                        <template
                                            v-if="
                                                props.deteccion
                                                    .tipo_actividad === 2
                                            "
                                        >
                                            <span>CURSO</span>
                                        </template>
                                        <template
                                            v-if="
                                                props.deteccion
                                                    .tipo_actividad === 3
                                            "
                                        >
                                            <span>CURSO-TALLER</span>
                                        </template>
                                        <template
                                            v-if="
                                                props.deteccion
                                                    .tipo_actividad === 4
                                            "
                                        >
                                            <span>FORO</span>
                                        </template>
                                        <template
                                            v-if="
                                                props.deteccion
                                                    .tipo_actividad === 5
                                            "
                                        >
                                            <span>SEMINARIO</span>
                                        </template>
                                        <template
                                            v-if="
                                                props.deteccion
                                                    .tipo_actividad === 6
                                            "
                                        >
                                            <span>DIPLOMADO</span>
                                        </template>
                                    </div>
                                    <div class="flow-root ... pt-5">
                                        <strong class="text-xl"
                                            >Tipo de solicitud:
                                        </strong>
                                        <template
                                            v-if="
                                                props.deteccion.tipo_FDoAP === 1
                                            "
                                        >
                                            <span>FORMACIÓN DOCENTE</span>
                                        </template>
                                        <template
                                            v-if="
                                                props.deteccion.tipo_FDoAP === 2
                                            "
                                        >
                                            <span
                                                >ACTUALIZACIÓN PROFESIONAL</span
                                            >
                                        </template>
                                    </div>
                                    <div class="flow-root ... pt-5">
                                        <strong class="text-xl"
                                            >Fechas en las que se realizara la
                                            actividad o evento:
                                        </strong>
                                        <span
                                            >Del
                                            {{ props.deteccion.fecha_I }} al
                                            {{ props.deteccion.fecha_F }}</span
                                        >
                                    </div>
                                    <div class="flow-root ... pt-5">
                                        <strong class="text-xl"
                                            >Horarios en las que se realizara la
                                            actividad o evento:
                                        </strong>
                                        <span
                                            >De {{ props.deteccion.hora_I }} a
                                            {{ props.deteccion.hora_F }}</span
                                        >
                                    </div>
                                    <div class="flow-root ... pt-5">
                                        <strong class="text-xl"
                                            >Total de horas de la actividad o
                                            evento:
                                        </strong>
                                        <span
                                            >{{
                                                props.deteccion.total_horas
                                            }}
                                            horas</span
                                        >
                                    </div>
                                    <div class="flow-root ... pt-5">
                                        <strong class="text-xl"
                                            >Objetivo de la actividad o evento:
                                        </strong>
                                        <v-divider></v-divider>
                                        <span>{{
                                            props.deteccion.objetivoEvento
                                        }}</span>
                                    </div>
                                    <template v-if="props.deteccion.obs === 1">
                                        <div class="flow-root ... pt-5">
                                            <strong class="text-xl"
                                                >Observaciones:
                                            </strong>
                                            <v-divider></v-divider>
                                            <span>{{
                                                props.deteccion.observaciones
                                            }}</span>
                                        </div>
                                    </template>
                                    <template
                                        v-if="props.deteccion.aceptado === 0"
                                    >
                                        <form @submit.prevent="addCorrecciones">
                                            <div class="flow-root ... pt-5">
                                                <strong class="text-xl"
                                                    >Añadir Correcciones:
                                                </strong>
                                                <TextAreaInput
                                                    id="correcion"
                                                    type="text"
                                                    class="mt-1 rounded w-full"
                                                    v-model="formC.correccion"
                                                    required
                                                />
                                                <PrimaryButton class="mt-4"
                                                    >Guardar.</PrimaryButton
                                                >
                                            </div>
                                        </form>
                                    </template>
                                    <template
                                        v-if="props.deteccion.aceptado === 0"
                                    >
                                        <form @submit.prevent="obs">
                                            <div class="flow-root ... pt-5">
                                                <strong class="text-xl"
                                                    >Añadir Observaciones:
                                                </strong>
                                                <TextInput
                                                    id="observaciones"
                                                    type="text"
                                                    class="mt-1 rounded w-full"
                                                    v-model="form.observaciones"
                                                    required
                                                />
                                                <PrimaryButton class="mt-4"
                                                    >Guardar.</PrimaryButton
                                                >
                                            </div>
                                        </form>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    </template>
</template>

<style scoped></style>
