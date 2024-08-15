<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { computed, onMounted, watch } from "vue";
import NavLink from "@/Components/NavLink.vue";
import Inscripcion from "../../dialogs/Inscripcion.vue";
import { ref } from "vue";
import { Curso } from "@/store/curso.js";
import DangerButton from "@/Components/DangerButton.vue";
import EliminarDeteccionConfirmation from "@/Pages/Views/dialogs/EliminarDeteccionConfirmation.vue";
import { router, useForm } from "@inertiajs/vue3";
import axios from "axios";
import CustomSnackBar from "@/Components/CustomSnackBar.vue";
import Loading from "@/Components/Loading.vue";
import Calificaciones from "@/Components/Calificaciones.vue";
import CalificacionesUpdate from "@/Components/CalificacionesUpdate.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {AlertLoading, errorMsg, notify, success_alert} from "@/jsfiels/alertas.js";
import {no} from "vuetify/locale";
import Swal from "sweetalert2";

const store = Curso();

const props = defineProps({
    curso: Object,
    auth: Object,
    docente: Array,
    inscritos: Array,
    errors: Object,
    flash: {
        type: [String, Object]
    }
});
const timeout = ref(0);

const dialog_inscripcion = ref(false);
const dialog = ref(false);
const dialogCalificacion = ref(false);
const snackbar = ref(false);
const loading = ref(false);
const calificacion = ref();
const color = ref("");
const message = ref("");
const curso_id = ref();
const docente = ref();
const dialogCalificacionUpdate = ref(false);
const show_ficha = ref(false);
const color_ficha = ref("");
const mm = ref([])
const reconocimiento = ref(false);
const getFacilitadores = ref([])
const reloadPage = () => {
    router.reload();
    snackbar.value = false;
};
const docente_calificacion = (docente_id) => {
    docente.value = docente_id;
    curso_id.value = props.curso.id;
    dialogCalificacion.value = true;
};
const update_docente_calificacion = (docente_id, calification) => {
    docente.value = docente_id;
    curso_id.value = props.curso.id;
    calificacion.value = calification;
    dialogCalificacionUpdate.value = true;
};
// const from_cdi = computed(() => {
//     for(let i = 0;i<=mm.value.length;i++){
//         return mm[i].value
//     }
// })
const snackEventActivator = () => {
    snackbar.value = true;
    message.value =
        "Parece que los recursos se han actualizado, por favor recarga la pagina";
    color.value = "warning";
    timeout.value = 8000;
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
const addCalificacion = (form) => {
    form.post(route("add.calificacion.desarrollo"), {
        onSuccess: () => {
            success_alert('Exito.', 'Calificación asignada.')
            dialogCalificacion.value = false
            form.reset()
        },
        onError: () => {
            notify('Alerta','warning', `${format_errors(props.errors)}`)
            message.value = ""
            dialogCalificacion.value = false
        },
    });
};
const updateCalificacion = (form) => {
    form.post(route("update.calificacion.desarrollo"), {
        onSuccess: () => {
            success_alert('Exito.', 'Calificación actualizada.')
            dialogCalificacionUpdate.value = false
            form.reset()
        },
        onError: () => {
            notify('Alerta','warning', `${format_errors(props.errors)}`)
            message.value = ""
            dialogCalificacionUpdate.value = false
        },
    });
};

const if_calificacion = computed(() => {
    return props.inscritos.every((inscrito) => inscrito.calificacion !== null);
});
const submit = (inscripcion, id) => {
    loading.value = true;
    axios
        .get(route("cdi.pdf"), {
            params: {
                docente: inscripcion,
                id_curso: id,
            },
        })
        .then((res) => {
            if (res.data[0] > 0){
               mm.value = res.data[1]
               notify('Advertencia', 'warning', `${res.data[1]}`)
               loading.value = false
            }else if (typeof res.data === 'string'){
                notify('¡Atención!', 'info', `${res.data}`)
            }else{
                success_alert('Exito.', 'Documento generado.')
            }
            const url = "/storage/CDI.pdf";
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", "CDI.pdf");
            document.body.appendChild(link);
            link.click();
            // success_alert('Exito.', 'Documento generado.')
            loading.value = false
        })
        .catch((error) => {
            loading.value = false;
            console.log(error.response.data)
            notify('¡Atención!','warning', `El servidor respondio: ${error.response.data}`)
            message.value = ""
        });
};

const generar_ficha = () => {
    loading.value = true;
    if(props.curso.ficha_tecnica) {
        axios
            .get(route("pdf.ficha.tecnica"), {
                params: {
                    id_ficha: props.curso.ficha_tecnica.id,
                },
            })
            .then((res) => {
                loading.value = false;
                const url = "/storage/ficha.pdf";
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute("download", "ficha.pdf");
                document.body.appendChild(link);
                link.click();
                notify('¡Atención!', 'info', `${res.data}`)
            })
            .catch((error) => {
                loading.value = false;
                errorMsg('¡Atención!', `El servidor respondio: ${error.response?.data}`)
            });
    } else {
        loading.value = false;
        notify('Atención', 'warning', 'El PDF no se puede generar debido a que no se ha capturado la ficha tecnica del curso.')
    }
};
const submitActa = () => {
    loading.value = true;
    axios
        .get(route("pdf.acta.calificaciones"), {
            params: {
                id: props.curso.id,
            },
        })
        .then((res) => {
            const url = "/storage/acta_de_calificaciones.pdf";
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", "acta calificaciones.pdf");
            document.body.appendChild(link);
            link.click();
            loading.value = false;
            notify('¡Atención!', `${res.data[1]}`, `${res.data[0]}`)
        })
        .catch((error) => {
            loading.value = false;
            notify('¡Atención!','warning', `El servidor respondio: ${error.response.data}`)
        });
};
const submitConstancia = (docente_id) => {
    loading.value = true;
    if(props.curso?.estado === 1 || props.curso?.estado === 2){
        axios
            .get(route("pdf.constancia"), {
                params: {
                    id: props.curso.id,
                    id_docente: docente_id,
                },
            })
            .then((res) => {
                if(res.data[1] === 0){
                    const url = "/storage/constancia.pdf";
                    const link = document.createElement("a");
                    link.href = url;
                    link.setAttribute("download", "constancia.pdf");
                    document.body.appendChild(link);
                    link.click();
                    loading.value = false;
                    success_alert('Exito', 'Se ha descargado la contancia con exito')
                }else{
                    loading.value = false;
                    console.log(res.data)
                    errorMsg('Parece que hacen falta estos parametros: ', `${res.data[0]}`)

                    // errorMsg('Parece que hacen falta estos parametros: ', `${}`)
                }
            })
            .catch((error) => {
                loading.value = false;
                notify('¡Atención!','warning', `El servidor respondio: ${error.response.data}`)
            });
    }else{
        notify('¡Atención!', 'info', 'El curso aun no ha comenzado por lo que no se puede generar la constancia.')
    }
};
const id_curso = useForm({
    curso: props.curso.id,
});
const desinscribir = (id) => {
    id_curso.post(`/docente/desinscribir/${id}`, {
        onSuccess: () => {
            success_alert('Exito.', `${props.flash?.message}`)
        },
        onError: () => {
            notify('Alerta','warning', `${format_errors(props.errors)}`)
        },
    });
};

const closeModal = () => {
    show_ficha.value = false;
};

const custom_snackbar = () => {
    snackbar.value = true;
    message.value = props.errors[0];
    color.value = "error";
    setTimeout(() => {
        snackbar.value = false;
    }, 5000);
};

function descargar_formato_constancia() {
    axios
        .get(route("formato.constancia"), {
            params: {
                id: props.curso?.id,
            },
        })
        .then((res) => {
            const url = "/storage/formato_constacia.xlsx";
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", "formato_constacia.xlsx");
            document.body.appendChild(link);
            link.click();
            loading.value = false;
            success_alert('Exito.', 'Excel generado.')
        })
        .catch((error) => {
            notify('Atención', 'warning', 'El archivo excel no se pudo generar, se debe revisar en el codigo fuente.')
        });
}
function descargar_formato_constancia_reconocimiento() {
    axios
        .get(route("formato.constancia.reconocimiento"), {
            params: {
                f: props.curso.id,
            },
        })
        .then((res) => {
            const url = "/storage/formato_constacia_reconocimiento.xlsx";
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute(
                "download",
                "formato_constacia_reconocimiento.xlsx"
            );
            document.body.appendChild(link);
            link.click();
            loading.value = false;
            success_alert('Exito.', 'Excel generado.')
        })
        .catch((error) => {
            notify('Atención', 'warning', 'El archivo excel no se pudo generar, se debe revisar en el codigo fuente.')
        });
}

function submit_inscripcion(form) {
    loading.value = true;
    form.post(route("inscribir.docente", props.curso.id), {
        onSuccess: () => {
            dialog_inscripcion.value = false;
            loading.value = false;
            success_alert('Exito.', 'Docente inscrito.')
            form.reset();
        },
        onError: () => {
            dialog_inscripcion.value = false;
            loading.value = false;
            notify('Alerta','warning', `${format_errors(props.errors)}`)
            message.value = ""
        },
    });
}

const IfEditFicha = () => {
    if(props.auth.user.docente_id && props.curso.id) {
        return true;
    }
}

const format_errors = (errors) => {
    for (const errorsKey in errors) {
        message.value += errors[errorsKey]
    }
    return message.value.split('.').join('. ');
}

const generar_reconocimiento = (facilitador_id) => {
    if (props.curso?.estado === 2){
        Swal.fire({
            title: 'Esta por generar el reconocimiento.',
            text: 'Esta acción puede tardar unos minutos.',
            showCancelButton: true,
            showConfirmButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar',
            icon: "info",
            timerProgressBar: true
        }).then(res => {
            if (res.isConfirmed){
                axios
                    .get(route("pdf.reconocimiento"), {
                        params: {
                            id: props.curso.id,
                            id_docente: facilitador_id,
                        },
                    })
                    .then((res) => {
                        if(res.data[1] === 0){
                            const url = "/storage/reconocimiento.pdf";
                            const link = document.createElement("a");
                            link.href = url;
                            link.setAttribute("download", "reconocimiento.pdf");
                            document.body.appendChild(link);
                            link.click();
                            loading.value = false;
                            success_alert('Exito', 'Se ha descargado la contancia con exito')
                        }else{
                            loading.value = false;
                            console.log(res.data)
                            errorMsg('Parece que hacen falta estos parametros: ', `${res.data[0]}`)

                            // errorMsg('Parece que hacen falta estos parametros: ', `${}`)
                        }
                    })
                    .catch((error) => {
                        loading.value = false;
                        notify('¡Atención!','warning', `El servidor respondio: ${error.response.data}`)
                    });
            }
        })
    }else{
        notify('¡Atención!', 'info', 'El curso no ha concluido por lo que no se puede generar el reconocimiento.')
    }
}

const descargar_lista_asistencia = () => {
    if (props.curso?.estado === 1 || props.curso?.estado === 2){
        Swal.fire({
            title: 'Esta por generar la lista de asistencia.',
            text: 'Esta acción puede tardar unos minutos.',
            showCancelButton: true,
            showConfirmButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar',
            icon: "info",
            timerProgressBar: true
        }).then(res => {
            if (res.isConfirmed){
                notify('¡Atención!', 'info', 'En breve se podra descargar la lista de asistencia.')
            }
        })
    }else{
        notify('¡Atención!', 'info', 'El curso aun no ha comenzado por lo que no se puede descargar la lista de asistencia.')
    }
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
    // store.inscritos_curso_desarrollo(props.curso?.id);
    axios
        .get('/api/v1/facilitadores/get', {
            params: {
                id: props.curso?.id
            }
        })
        .then((res) => {
            getFacilitadores.value = res.data
            console.log(res.data);
        })
        .catch((err) => {
            console.log(err.response.data);
        });
});

const close_modal = () => {
    reconocimiento.value = false
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="grid grid-rows-1">
                <div class="flex justify-start">
                    <div class="grid grid-cols-1">
                        <template
                            v-if="props.curso.estado === 0 || props.curso.estado === 1"
                        >
                            <div class="flex justify-center sm:justify-start">
                                <NavLink
                                    :href="route('index.desarrollo.cursos')"
                                    as="button"
                                    type="button"
                                >
                                    <v-btn icon color="blue-darken-1">
                                        <v-icon>mdi-arrow-left</v-icon>
                                    </v-btn>
                                </NavLink>
                            </div>
                        </template>
                        <template v-if="props.curso.estado === 2">
                            <div class="flex justify-center sm:justify-start">
                                <NavLink
                                    :href="route('index.registros.c')"
                                    as="button"
                                    type="button"
                                >
                                    <v-btn icon color="blue-darken-1">
                                        <v-icon>mdi-arrow-left</v-icon>
                                    </v-btn>
                                </NavLink>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <div class="grid grid-rows-1">
                <div class="grid grid-cols-1">
                    <div class="flex justify-center ma-5">
                        <h2 class="text-2xl font-medium text-gray-900">
                            {{ props.curso.nombreCurso }}
                        </h2>
                    </div>
                </div>
            </div>
                <div class="grid grid-cols-1 md:grid-cols-4">
                    <div class="flex justify-center items-center m-3">

                    </div>
                </div>
        </template>
        <div class="grid grid-rows-1">
            <div class="flex justify-center">
                <div class="grid grid-cols-1 lg:grid-cols-3 p-2 gap-2 p-6">
                    <div class="flex justify-center items-center">
                        <v-tooltip location="right">
                            <template v-slot:activator="{ props }">
                                <v-btn
                                    prepend-icon="mdi-microsoft-excel"
                                    v-bind="props"
                                    color="green-lighten-1"
                                    @click="descargar_formato_constancia"
                                >
                                    Formato de constancia
                                </v-btn>
                            </template>
                            <span>Descargar</span>
                        </v-tooltip>
                    </div>
                    <div class="flex justify-center items-center">
                        <v-tooltip location="right">
                            <template v-slot:activator="{ props }">
                                <v-btn
                                    prepend-icon="mdi-microsoft-excel"
                                    v-bind="props"
                                    color="green-lighten-1"
                                    @click="descargar_formato_constancia_reconocimiento"
                                >
                                    Formato de reconocimiento
                                </v-btn>
                            </template>
                            <span>Descargar</span>
                        </v-tooltip>
                    </div>
                    <div class="flex justify-center items-center">
                        <v-tooltip location="right">
                            <template v-slot:activator="{ props }">
                                <v-btn
                                    prepend-icon="mdi-file-pdf-box"
                                    v-bind="props"
                                    color="blue-darken-1"
                                    @click="descargar_lista_asistencia"
                                >
                                    Generar lista de asistencia.
                                </v-btn>
                            </template>
                            <span>Descargar</span>
                        </v-tooltip>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 mt-7 sm:p-8 bg-white shadow sm:rounded-lg">
                <h2 class="text-lg font-medium text-gray-900">
                    Docentes inscritos a este curso
                </h2>
                <div class="grid grid-rows-1">
                    <div class="flex justify-start mt-3 mb-5">
                        <div class="flex justify-end items-end mt-2">
                            <v-btn
                                @click="dialog_inscripcion = true"
                                block
                                size="large"
                                color="blue-darken-1"
                            >Inscribir</v-btn
                            >
                        </div>
                        <Inscripcion
                            :errors="$page.props.errors"
                            :auth="props.auth.user"
                            :curso="props.curso"
                            :docente="props.docente"
                            v-model="dialog_inscripcion"
                            @update:modelValue="dialog_inscripcion = $event"
                            @custom:snackbar="custom_snackbar"
                            @form:inscripcion="submit_inscripcion"
                        ></Inscripcion>
                    </div>
                    <div class="flex justify-end mb-5">
                        <div class="flex justify-end items-end">
                            <v-btn
                                @click="reconocimiento = true"
                                block
                                size="large"
                                color="blue-darken-1"
                            >Generar reconocimiento facilitadores.</v-btn
                            >
                            <Modal :show="reconocimiento" @close="close_modal">
                                <div class="grid grid-rows-1">
                                    <div class="flex justify-center">
                                        <div class="grid grid-cols-1">
                                            <p class="p-5 text-xl">Facilitadores de este curso</p>
                                            <div class="p-10">
                                                <table class="border-collapse border border-slate-500">
                                                    <thead>
                                                    <tr>
                                                        <th class="border border-slate-600 p-2">ID</th>
                                                        <th class="border border-slate-600">Nombre</th>
                                                        <th class="border border-slate-600 p-2">Reconocimiento</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr v-for="f in getFacilitadores" :key="f.id">
                                                        <td class="border border-slate-600 p-2">
                                                            {{f.id}}
                                                        </td>
                                                        <td class="border border-slate-600 p-2 text-center">
                                                            {{f.nombre_completo}}
                                                        </td>
                                                        <td class="border border-slate-600 p-2 text-center">
                                                            <v-btn
                                                                icon="mdi-file-pdf-box"
                                                                color="success"
                                                                @click="generar_reconocimiento(f.id)"
                                                            >
                                                            </v-btn>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Modal>
                        </div>
                    </div>
                    <div class="flex justify-end mb-10">
                        <template v-if="props.inscritos.length !== 0">
                            <v-btn
                                color="blue-darken-1"
                                @click="submitActa"
                                :disabled="!if_calificacion"
                            >Descargar Acta de Calificaciones</v-btn
                            >
                        </template>
                    </div>
                </div>
                <v-table fixed-header height="500px" hover>
                    <thead>
                        <tr>
                            <th class="text-center">Nombre</th>
                            <th class="text-center">Apellido Paterno</th>
                            <th class="text-center">Apellido Materno</th>
                            <th class="text-center">CÉDULA DE INSCRIPCIÓN</th>
                            <th class="text-center">Desinscribir</th>
                            <th class="text-center">CALIFICACIÓN</th>
                            <th class="text-center">Constancia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="inscrito in props.inscritos"
                            :key="inscrito.id"
                        >
                            <td class="text-center">{{ inscrito.nombre }}</td>
                            <td class="text-center">
                                {{ inscrito.apellidoPat }}
                            </td>
                            <td class="text-center">
                                {{ inscrito.apellidoMat }}
                            </td>
                            <td>
                                <div class="flex justify-center items-center">
                                    <v-btn
                                        prepend-icon="mdi-file-pdf-box"
                                        color="blue-darken-1"
                                        @click="
                                            submit(inscrito.id, props.curso.id)
                                        "
                                    >
                                        GENERAR PDF
                                    </v-btn>
                                </div>
                            </td>
                            <td>
                                <div class="flex justify-center items-center">
                                    <!--                                <NavLink :href="route('delete.inscripcion', inscrito.id)" as="button">-->
                                    <v-btn
                                        icon="mdi-account-arrow-left"
                                        color="blue-darken-1"
                                        @click="desinscribir(inscrito.id)"
                                    >
                                    </v-btn>
                                    <!--                                </NavLink>-->
                                </div>
                            </td>
                            <td class="text-center">
                                <template v-if="inscrito.calificacion === null">
                                    <v-btn
                                        icon
                                        @click="
                                            docente_calificacion(inscrito.id)
                                        "
                                    >
                                        <v-icon>mdi-pencil-plus</v-icon>
                                    </v-btn>
                                </template>
                                <template
                                    v-else-if="inscrito.calificacion === 0"
                                >
                                    <v-chip
                                        class="ma-2"
                                        color="red"
                                        text-color="white"
                                        @click="
                                            update_docente_calificacion(
                                                inscrito.id,
                                                inscrito.calificacion
                                            )
                                        "
                                    >
                                        <p class="text-center">NO APROBADO</p>
                                    </v-chip>
                                </template>
                                <template
                                    v-else-if="inscrito.calificacion === 1"
                                >
                                    <v-chip
                                        class="ma-2"
                                        color="success"
                                        text-color="white"
                                        @click="
                                            update_docente_calificacion(
                                                inscrito.id,
                                                inscrito.calificacion
                                            )
                                        "
                                    >
                                        <p class="text-center">APROBADO</p>
                                    </v-chip>
                                </template>
                            </td>
                            <td class="text-center">
                                <template v-if="inscrito?.calificacion === 1">
                                    <v-btn
                                        icon="mdi-file-pdf-box"
                                        color="success"
                                        @click="submitConstancia(inscrito.id)"
                                    >
                                    </v-btn>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </v-table>
            </div>
        </div>
        <div class="mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 mt-7 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="grid grid-rows-1">
                    <div class="flex justify-center p-2">
                        <p class="text-2xl">Ficha técnica</p>
                    </div>
                    <div class="flex justify-center">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2">
                            <div class="flex justify-center items-center">
                                <template v-if="!props.curso.ficha_tecnica">
                                    <NavLink
                                        :href="
                                    route('crear.ficha', [
                                        props.auth.user.docente_id,
                                        props.curso?.id,
                                    ])
                                "
                                        as="button"
                                    >
                                        <v-btn color="blue-darken-1">
                                            Crear ficha técnica
                                        </v-btn>
                                    </NavLink>
                                </template>
                                <template v-else>
                                    <v-btn color="blue-darken-1" @click="notify('¡Atención!', 'warning', 'La ficha técnica ya ha sido creada.')">
                                        Crear ficha técnica
                                    </v-btn>
                                </template>
                            </div>
                            <div class="flex justify-center items-center">
                                <v-btn
                                    color="blue-darken-1"
                                    @click="show_ficha = true"
                                >
                                    Ver ficha técnica
                                </v-btn>
                                <Modal :show="show_ficha" @close="closeModal">
                                    <template v-if="props.curso?.ficha_tecnica">
                                        <div class="grid grid-rows-3">
                                            <div
                                                class="flex justify-center items-center pa-4"
                                            >
                                                {{
                                                    props.curso?.ficha_tecnica
                                                        .competencias_desarrollar
                                                }}
                                            </div>
                                            <div
                                                class="flex justify-center items-center pa-4"
                                            >
                                                {{
                                                    props.curso?.ficha_tecnica
                                                        .objetivo_general
                                                }}
                                            </div>
                                            <div
                                                class="flex justify-end items-center pa-4"
                                            >
                                                <div class="grid grid-cols-2">
                                                    <div
                                                        class="flex justify-center"
                                                    >
                                                        <SecondaryButton
                                                            @click="
                                                            show_ficha = false
                                                        "
                                                        >Cerrar</SecondaryButton
                                                        >
                                                    </div>
                                                    <div
                                                        class="flex justify-center"
                                                    >
                                                        <NavLink
                                                            :href="
                                                            route(
                                                                'eliminar.ficha',
                                                                props.curso?.ficha_tecnica
                                                                    .id
                                                            )
                                                        "
                                                            as="button"
                                                            method="delete"
                                                        >
                                                            <danger-button
                                                            >Eliminar</danger-button
                                                            >
                                                        </NavLink>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <template
                                        v-else-if="
                                        props.curso?.ficha_tecnica === null
                                    "
                                    >
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
                                <v-btn
                                    @click="generar_ficha"
                                    color="blue-darken-1"
                                    prepend-icon="mdi-file-pdf-box"
                                >
                                    Descargar PDF
                                </v-btn>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 mt-7 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex items-center">
                    <div>
                        <div class="p-5">
                            <div class="flow-root ...">
                                <strong
                                    >Asignaturas en la que se requiere formación
                                    o actualización:
                                </strong>
                                <v-divider></v-divider>
                                <span>{{ props.curso.asignaturaFA }}</span>
                            </div>
                            <div class="flow-root ... pt-7">
                                <strong
                                    >Contenidos temáticos en que se requiere la
                                    formación o actualización:
                                </strong>
                                <v-divider></v-divider>
                                <span>{{ props.curso.contenidosTM }}</span>
                            </div>
                            <div class="flow-root ... pt-7">
                                <strong
                                    >Número de profesores(as) que la requieren:
                                </strong>
                                <span>{{ props.curso.numeroProfesores }}</span>
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
                                <strong
                                    >Periodo en el que se requiere la formación
                                    o actualización (enero-junio o agosto
                                    diciembre):
                                </strong>
                                <template v-if="props.curso.periodo === 1">
                                    <span>ENERO-JUNIO</span>
                                </template>
                                <template v-if="props.curso.periodo === 2">
                                    <span>AGOSTO-DICIEMBRE</span>
                                </template>
                            </div>
                            <div class="flow-root ... pt-5">
                                <strong>Carrera a la que va dirigido: </strong>
                                <span>{{
                                    props.curso.carrera.nameCarrera
                                }}</span>
                            </div>
                            <div class="flow-root ... pt-5">
                                <strong
                                    >Lugar en el que se realizara el curso:
                                </strong>
                                <template v-if="props.curso.lugar !== null">
                                    <span>{{
                                        props.curso.lugar.nombreAula
                                    }}</span>
                                </template>
                                <template v-if="props.curso.lugar === null">
                                    <span
                                        >A este curso no se le ha asignado una
                                        sala</span
                                    >
                                </template>
                            </div>
                            <template v-if="props.curso?.deteccion_facilitador.length > 0">
                                <div class="flow-root ... pt-5">
                                    <strong>Facilitador(es): </strong>
                                    <div
                                        v-for="facilitador in props.curso
                                        .deteccion_facilitador"
                                    >
                                    <span
                                    >{{ facilitador.nombre }}
                                        {{ facilitador.apellidoPat }}
                                        {{ facilitador.apellidoMat }}</span
                                    >
                                    </div>
                                </div>
                            </template>
                            <template
                                v-if="props.curso.facilitador_externo != null"
                            >
                                <div class="flow-root ... pt-5">
                                    <strong>Facilitador externo: </strong>
                                    <span>{{
                                        props.curso.facilitador_externo
                                    }}</span>
                                </div>
                            </template>
                            <div class="flow-root ... pt-5">
                                <strong
                                    >Tipo de curso, taller, conferencias, etc:
                                </strong>
                                <template
                                    v-if="props.curso.tipo_actividad === 1"
                                >
                                    <span>TALLER</span>
                                </template>
                                <template
                                    v-if="props.curso.tipo_actividad === 2"
                                >
                                    <span>CURSO</span>
                                </template>
                                <template
                                    v-if="props.curso.tipo_actividad === 3"
                                >
                                    <span>CURSO-TALLER</span>
                                </template>
                                <template
                                    v-if="props.curso.tipo_actividad === 4"
                                >
                                    <span>FORO</span>
                                </template>
                                <template
                                    v-if="props.curso.tipo_actividad === 5"
                                >
                                    <span>SEMINARIO</span>
                                </template>
                                <template
                                    v-if="props.curso.tipo_actividad === 6"
                                >
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
                                <strong
                                    >Fechas en las que se realizara la actividad
                                    o evento:
                                </strong>
                                <span
                                    >Del {{ props.curso.fecha_I.split('-').reverse().join('/') }} al
                                    {{ props.curso.fecha_F.split('-').reverse().join('/') }}</span
                                >
                            </div>
                            <div class="flow-root ... pt-5">
                                <strong
                                    >Horarios en las que se realizara la
                                    actividad o evento:
                                </strong>
                                <span
                                    >De {{ props.curso.hora_I }} a
                                    {{ props.curso.hora_F }}</span
                                >
                            </div>
                            <div class="flow-root ... pt-5">
                                <strong
                                    >Objetivo de la actividad o evento:
                                </strong>
                                <v-divider></v-divider>
                                <span>{{ props.curso.objetivoEvento }}</span>
                            </div>
                            <template v-if="props.curso.obs === 1">
                                <div class="flow-root ... pt-5">
                                    <strong>Observaciones: </strong>
                                    <v-divider></v-divider>
                                    <span>{{ props.curso.observaciones }}</span>
                                </div>
                            </template>
                        </div>

                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-1 p-5 m-5">
                        <div class="flex justify-center md:justify-end mr-3 p-2">
                            <NavLink
                                :href="route('curso.editar', props.curso.id)"
                                as="button"
                                type="button"
                            >
                                <button class="rounded-lg text-center bg-blue-500 text-white p-5 hover:bg-blue-800">
                                    Editar
                                </button>
                            </NavLink>
                        </div>
                        <div class="flex justify-center md:justify-start mr-3">
                            <div class="flex items-center">
                                <button class="rounded-lg text-center bg-red-500 text-white p-4 hover:bg-blue-800" @click="dialog = true">
                                    Eliminar
                                </button>
                            </div>
                            <EliminarDeteccionConfirmation
                                v-model="dialog"
                                :curso="props.curso.id"
                                @update:modelValue="dialog = $event"
                            ></EliminarDeteccionConfirmation>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Calificaciones
            v-model="dialogCalificacion"
            :curso="curso_id"
            :docente="docente"
            @form:Calificacion="addCalificacion"
            @update:modelValue="dialogCalificacion = $event"
        ></Calificaciones>

        <CalificacionesUpdate
            v-model="dialogCalificacionUpdate"
            :calificacion="calificacion"
            :curso="curso_id"
            :docente="docente"
            @update:Calificacion="updateCalificacion"
            @update:modelValue="dialogCalificacionUpdate = $event"
        ></CalificacionesUpdate>

        <CustomSnackBar
            :message="message"
            :color="color"
            v-model="snackbar"
            @update:modelValue="snackbar = $event"
            :timeout="timeout"
        >
            <!-- <template v-slot:reloadingbutton>
                <div class="flex justify-start pa-1">
                    <v-btn @click="reloadPage" icon="mdi-reload"></v-btn>
                </div>
            </template> -->
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
    </AuthenticatedLayout>
</template>

<style scoped></style>
