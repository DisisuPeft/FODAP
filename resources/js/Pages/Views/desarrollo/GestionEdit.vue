<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TablaCarrera from "@/Pages/Views/desarrollo/tablas/TablaCarrera.vue";
import NavLink from "@/Components/NavLink.vue";
import { router, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {onMounted, ref, watch} from "vue";
import TablaDepartamento from "@/Pages/Views/desarrollo/tablas/TablaDepartamento.vue";
import TablaLugares from "@/Pages/Views/desarrollo/tablas/TablaLugares.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TablaUsuariosCoordinacion from "@/Pages/Views/desarrollo/tablas/TablaUsuariosCoordinacion.vue";
import TablaUsuariosDocente from "@/Pages/Views/desarrollo/tablas/TablaUsuariosDocente.vue";
import TablaUsuariosAcademicos from "@/Pages/Views/desarrollo/tablas/TablaUsuariosAcademicos.vue";
import FormSubdireccion from "@/Pages/Views/dialogs/DialogSubdireccion.vue";
import TablaSub from "@/Pages/Views/desarrollo/tablas/TablaSub.vue";
import TablaDirector from "@/Pages/Views/desarrollo/tablas/TablaDirector.vue";
import DialogDirector from "@/Pages/Views/dialogs/DialogDirector.vue";
import CustomSnackBar from "@/Components/CustomSnackBar.vue";
import NombreInstitutoTable from "@/Pages/Views/desarrollo/tablas/NombreInstitutoTable.vue";
import DialogNombreInstituto from "@/Pages/Views/dialogs/DialogNombreInstituto.vue";
import Swal from "sweetalert2";
import {AlertLoading, errorMsg, notify, success_alert, AlertUploadingImage} from "@/jsfiels/alertas.js";
import CreateCarrera from "@/Pages/Views/desarrollo/forms/CreateCarrera.vue";
import Modal from "@/Components/Modal.vue";
import UpdateProfileInformationFormSelected
    from "@/Pages/Views/desarrollo/forms/UpdateProfileInformationFormSelected.vue";
import UpdatePasswordFormSelected from "@/Pages/Views/desarrollo/forms/UpdatePasswordFormSelected.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const search = ref("");
const dialogSub = ref(false);
const dialogDirector = ref(false);
const snackbar = ref(false);
const message = ref("");
const color = ref();
const timeout = ref(0);
const dialogInstituto = ref(false);
const create_carrera = ref(false)
const create_cuenta = ref(false)
const passwordFielType = ref("password");
const passwordInput = ref(null);
const currentPasswordInput = ref(null);
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
const props = defineProps({
    docente: Array,
    carrera: Array,
    departamento: Array,
    auth: Object,
    lugar: Array,
    users: Array,
    sub: Array,
    fechas: Object,
    errors: Object,
    director: Array,
    instituto: Array,
    roles: Array
});

const form = useForm({
    fecha_Inicio: "",
    fecha_Final: "",
});

const form_file = useForm({
    file: null,
});
const form_file_acta_img = useForm({
    file: null,
});
const form_file_constancia_img = useForm({
    file: null,
});
const img_logo_ittg = useForm({
    file: null,
});
const img_logo_tecnm = useForm({
    file: null,
});
const img_logo_educacion = useForm({
    file: null,
});
const crear_carrera = () => {
    Swal.fire({
        title: '¿Ingreso la información correctas?',
        text: 'Esta acción se puede revertir',
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
        icon: "info",
        timerProgressBar: true
    }).then(res => {
        if (res.isConfirmed){
            AlertLoading('Guardando los datos...', 'Esta accion puede tardar unos minutos')
            form_carrera.post(route("store.carrera"), {
                onSuccess: () => {
                    form.reset();
                    success_alert('Exito', 'El área académica fue creada con exito.')
                    router.reload()
                },
                onError: () => {
                    // console.log(props.errors)
                    errorMsg('Error', `${format_errors(props.errors)}`)
                    message.value = ""
                },
            });
        }
    })
}


const form_carrera = useForm({
    departamento_id: null,
    nombre_carrera: "",
    presidente_academia: null
})
const close_modal = () => {
    create_carrera.value = false
}

function submit() {
    Swal.fire({
        title: '¿Ingreso las fechas correctas?',
        text: 'Esta acción se puede revertir',
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
        icon: "info",
    }).then(res => {
        if (res.isConfirmed){
            AlertLoading('Guardando los datos...', 'Esta accion puede tardar unos minutos')
            form.post(route("config.dates"), {
                onSuccess: () => {
                    form.reset();
                    success_alert('Fechas actualizadas', 'Las fechas fueron establecidas con exito.')
                    // router.reload()
                },
                onError: () => {
                    errorMsg('Error', `${format_errors(props.errors)}`)
                    message.value = ''
                },
            });
        }
    })
}

const upload_cvu = () => {
    form_file.post(route("subir.cvu"), {
        forceFormData: true,
        onSuccess: () => {
            form_file.reset();
            snackSuccessActivator();
        },
        onError: () => {
            snackErrorActivator();
        },
    });
};
const upload_acta = () => {
    Swal.fire({
        title: '¿Esta seguro que desea subir la imagen?',
        text: 'Esta accion se puede revertir',
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar'
    }).then(res => {
        if (res.isConfirmed){
            AlertUploadingImage('Subiendo imagen', 'Esta accion puede tardar unos minutos')
            form_file_acta_img.post(route("subir.actacalificaciones"), {
                forceFormData: true,
                onSuccess: () => {
                    form_file_acta_img.reset();
                    success_alert('Exito', 'La hoja membretada se ha subido con exito');
                },
                onError: () => {
                    form_file_acta_img.reset();
                    errorMsg('Error', `${format_errors(props.errors)}`)
                },
            });
        }
    })
};
const upload_constancia = () => {
    Swal.fire({
        title: '¿Esta seguro que desea subir la imagen?',
        text: 'Esta accion se puede revertir',
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
    }).then(res => {
        if (res.isConfirmed){
            AlertUploadingImage('Subiendo imagen', 'Esta accion puede tardar unos minutos')
            form_file_constancia_img.post(route("subir.constancia"), {
                forceFormData: true,
                onSuccess: () => {
                    form_file_constancia_img.reset();
                    success_alert('Exito', 'La hoja membretada se ha subido con exito');
                },
                onError: () => {
                    form_file_constancia_img.reset();
                    errorMsg('Error', `${format_errors(props.errors)}`)
                },
            });
        }
    })
};
const upload_logotec = () => {
    img_logo_ittg.post(route("subir.logoTec"), {
        forceFormData: true,
        onSuccess: () => {
            img_logo_ittg.reset();
            snackSuccessActivator();
        },
        onError: () => {
            errorMsg('Error', 'Ha ocurrido un error al subir la ima')
        },
    });
};
const upload_tecnm = () => {
    img_logo_tecnm.post(route("subir.logoTecnm"), {
        forceFormData: true,
        onSuccess: () => {
            img_logo_tecnm.reset();
            snackSuccessActivator();
        },
        onError: () => {
            snackErrorActivator();
        },
    });
};
const upload_educacion = () => {
    img_logo_educacion.post(route("subir.logoEducacion"), {
        forceFormData: true,
        onSuccess: () => {
            img_logo_educacion.reset();
            snackSuccessActivator();
        },
        onError: () => {
            snackErrorActivator();
        },
    });
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
});

// watch(() => create_carrera.value, (newVal) => {
//     watch_variable.value = newVal;
//     console.log(newVal)
// });
const format_errors = (errors) => {
    for (const errorsKey in errors) {
        message.value += errors[errorsKey]
    }
    return message.value.split('.').join('. ');
}

const open_modal_cuenta = () => {
    Swal.fire({
        title: '¿Esta seguro que desea crear un nuevo usuario académico?',
        text: 'El correo debe ser proporcionado por el área de computo y revisado por la coordinación de formación docente.',
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
        icon: "info",
    }).then(res => {
        if (res.isConfirmed){
            // AlertLoading('Cargando formulario', 'Esta accion puede tardar unos minutos')
            create_cuenta.value = true
        }
    })
}

const close_modal_cuenta = () => {
    create_cuenta.value = false
}
const form_cuenta = useForm({
    email: "",
    docente_id: null,
    password: '',
    departamento_id: null,
    role: null,
});
const crear_cuenta = () => {
    Swal.fire({
        title: '¿Ingreso la información correcta?',
        text: 'Esta acción se puede revertir',
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
        icon: "info",
        timerProgressBar: true
    }).then(res => {
        if (res.isConfirmed){
            AlertLoading('Guardando los datos...', 'Esta accion puede tardar unos minutos')
            form_cuenta.post(route('create.user.academico'), {
                onSuccess: () => {
                    form.reset();
                    success_alert('Exito', 'El usuario fue creado con exito.')
                    // router.reload()
                },
                onError: () => {
                    // console.log(props.errors)
                    notify('Alerta','warning', `${format_errors(props.errors)}`)
                    message.value = ""
                },
            });
        }
    })
}
const show_visibilty = () => {
    passwordFielType.value = passwordFielType.value === "password" ? "text" : "password";
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 mt-16 pt-16">
<!--            Fechas-->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <header>
                    <div class="flex justify-center m-2">
                        <h2 class="text-lg font-medium text-gray-900">
                            Establecer fechas para la captura de Deteccion de
                            Necesidades
                        </h2>
                    </div>
                </header>
                <div class="grid grid-rows-1 md:grid-rows-2">
                    <div class="flex justify-start">
                        <strong class="m-2"
                        >Ultimas fechas establecidas:
                        </strong>
                    </div>
                    <template v-if="props.fechas !== null">
                        <div class="grid grid-cols-1 md:grid-cols-2 m-2">
                            <div class="flex justify-end">
                                <p>
                                    {{ props.fechas.fecha_inicio?.split('-').reverse().join('/') }}
                                </p>
                                <p class="ml-5">-</p>
                            </div>
                            <div class="flex justify-start">
                                <p class="ml-4">
                                    {{ props.fechas.fecha_final?.split('-').reverse().join('/') }}
                                </p>
                            </div>
                        </div>
                    </template>
                    <template v-if="props.fechas === null">
                        <div class="grid grid-rows-1">
                            <div class="flex justify-center">
                                <p class="text-xl">Sin fechas establecidas</p>
                            </div>
                        </div>
                    </template>
                </div>
                <form @submit.prevent="submit">
                    <div class="grid grid-rows-2">
                        <div class="flex justify-center">
                            <div class="grid grid-cols-1 md:grid-cols-2">
                                <div class="flex justify-center m-2">
                                    <div class="flex justify-center items-center m-2">
                                        <InputLabel
                                            for="inicio"
                                            value="Inicia: "
                                        ></InputLabel>
                                    </div>
                                    <input class="mt-1 block w-[300px] px-3 py-2 bg-white border border-slate-300 rounded-md text-sm shadow-sm placeholder-slate-400
                                    focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500" type="date" v-model="form.fecha_Inicio">
                                </div>
                                <div class="flex justify-center m-2">
                                    <div class="flex justify-center items-center m-2">
                                        <InputLabel
                                            class="text-lg"
                                            for="termino"
                                            value="Termina: "
                                        ></InputLabel>
                                    </div>
                                    <input class="mt-1 block w-[300px] px-3 py-2 bg-white border border-slate-300 rounded-md text-sm shadow-sm placeholder-slate-400
                                    focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500" type="date" v-model="form.fecha_Final">
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end items-center mr-10">
                            <button class="rounded-md bg-sky-500 hover:bg-sky-700 p-4 text-white">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
<!--            -->
<!--        carreras-->
            <div class="p-4 mt-7 sm:p-8 bg-white shadow sm:rounded-lg">
                <header>
                    <h2 class="text-lg font-medium text-gray-900">
                        Aréas académicas
                    </h2>

                    <p class="mt-1 text-sm text-gray-600">
                        Actualizar o añadir
                    </p>
                </header>
                <TablaCarrera :carrera="props.carrera"></TablaCarrera>
                <div
                    class="flex justify-end mr-12 mt-12 h-6 items-center gap-4"
                >
                    <button class="rounded-md bg-sky-500 hover:bg-sky-700 p-4 text-white" @click="create_carrera = true">Crear aréa académica</button>
                    <Modal :show="create_carrera" @close="close_modal">
                        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                            <div class="p-4 mt-7 sm:p-8 bg-white shadow sm:rounded-lg">
                                <form class="mt-6 space-y-6" @submit.prevent="crear_carrera">
                                <div class="container mx-auto px-4">
                                    <div class="grid grid-rows-1">
                                        <div class="grid grid-cols-1">
                                            <div class="flex justify-start p-2">
                                                <InputLabel for="nombre_carrera" value="Nombre de la carrera: "/>
                                            </div>
                                            <div class="flex justify-center">
                                                <input class="mt-1 block px-3 py-2 bg-white border border-slate-200 rounded-md text-sm shadow-2xl placeholder-slate-400
                                                                                focus:outline-none focus:border-sky-950 focus:ring-1 focus:ring-sky-500 w-full" v-model="form_carrera.nombre_carrera">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1">
                                            <div class="flex justify-start p-2">
                                                <InputLabel for="presidente_academia" value="Presidente de la academia"/>
                                            </div>
                                            <div class="flex justify-center">
                                                <v-autocomplete v-model="form_carrera.presidente_academia" :items="props.docente" item-title="nombre_completo" item-value="id" variant="solo"></v-autocomplete>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1">
                                            <div class="flex justify-start p-2">
                                                <InputLabel for="presidente_academia" value="Departamento al que la carrera pertenece"/>
                                            </div>
                                            <div class="flex justify-center">
                                                <v-select v-model="form_carrera.departamento_id" :items="props.departamento" item-value="id" item-title="nameDepartamento" variant="solo"></v-select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <div class="grid grid-rows-1">
                                        <div class="grid grid-cols-2">
                                            <!--                            <NavLink :href="route('parametros.edit')">-->
                                            <div class="flex justify-end">
                                                <button class="rounded-md bg-red-500 hover:bg-red-700 p-4 text-white" @click="close_modal">Cancelar</button>
                                            </div>
                                            <div class="flex justify-center">
                                                <!--                                            <v-col cols="2">-->
                                                <v-btn type="submit" color="blue-darken-1" icon="mdi-content-save-all" size="x-large" class="floating-btn" @click="crear_carrera">

                                                </v-btn>

                                                <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
                                                    <p v-if="form_carrera.recentlySuccessful" class="text-sm text-gray-600">Guardado.</p>
                                                </Transition>
                                                <!--                                            </v-col>-->
                                            </div>
                                            <!--                            </NavLink>-->
                                        </div>
                                    </div>
                                </div>
                                                </form>
                            </div>
                        </div>
                    </Modal>
                </div>
            </div>
<!--            -->
<!-- departamentos-->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <header>
                    <h2 class="text-lg font-medium text-gray-900">
                        Departamentos
                    </h2>

                    <p class="mt-1 text-sm text-gray-600">
                        Actualizar o añadir departamentos
                    </p>
                </header>
                <TablaDepartamento
                    :departamento="props.departamento"
                ></TablaDepartamento>
                <div
                    class="flex justify-end mr-12 mt-12 h-6 items-center gap-4"
                >
                    <NavLink :href="route('create.departamento')">
                        <PrimaryButton>Crear un departamento</PrimaryButton>
                    </NavLink>
                </div>
            </div>
<!--            -->
<!--            Lugar de realizacion-->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <header>
                    <h2 class="text-lg font-medium text-gray-900">Lugar</h2>

                    <p class="mt-1 text-sm text-gray-600">
                        Lugares donde se llevan a cabo cursos y actividades.
                    </p>
                </header>
                <TablaLugares :lugar="props.lugar"></TablaLugares>
                <div class="flex justify-end mt-8 mr-12 items-center">
                    <NavLink :href="route('create.lugar')" as="button">
                        <primary-button>Crear</primary-button>
                    </NavLink>
                </div>
            </div>
<!--               -->
<!--            Cuentas de usuarios academicos-->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <header class="p-2">
                    <h2 class="text-xl font-medium text-gray-900 mb-8">
                        Usuarios
                    </h2>

                    <strong class="mt-1 text-lg text-gray-600 p-2">
                        Jefes de departamento.
                    </strong>
                </header>
                <div class="flex justify-center">
                    <v-text-field
                        label="Buscar"
                        variant="solo"
                        v-model="search"
                        clearable
                    ></v-text-field>
                </div>
                <TablaUsuariosAcademicos
                    :users="props.users"
                    :search="search"
                    :errors="props.errors"
                ></TablaUsuariosAcademicos>
                <div class="grid grid-rows-1 p-4">
                    <div class="flex justify-end">
                        <div class="grid grid-cols-1">
                            <button class="rounded-md bg-sky-500 hover:bg-sky-700 p-4 text-white" @click="open_modal_cuenta">Crear correo de jefe académico</button>
                            <Modal :show="create_cuenta" @close="close_modal_cuenta">
                                <div class="grid grid-rows-1">
                                    <div class="flex justify-end">
                                        <v-btn
                                            elevation="0"
                                            size="large"
                                            icon
                                            @click="close_modal_cuenta"
                                        >
                                            <v-icon>mdi-close</v-icon>
                                        </v-btn>
                                    </div>
                                </div>
                                <div class="grid grid-rows-1">
                                    <div class="flex justify-center">
                                        <p class="text-2xl">Crear nuevo usuario</p>
                                    </div>
                                </div>
                                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                                        <form class="space-y-6" @submit.prevent="crear_cuenta">
                                            <div class="container mx-auto px-4">
                                                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                                                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                                                        <form @submit.prevent="crear_cuenta" class="space-y-6">
                                                            <div>
                                                                <InputLabel for="email" value="Correo Institucional" />

                                                                <TextInput
                                                                    id="email"
                                                                    type="email"
                                                                    class="mt-1 block w-full"
                                                                    v-model="form_cuenta.email"
                                                                    required
                                                                    autocomplete="username"
                                                                />

                                                                <InputError class="mt-2" :message="form_cuenta.errors.email" />
                                                            </div>
                                                            <div>
                                                                <InputLabel for="password" value="Nueva contraseña" />

                                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                                                    <div class="flex justify-start">
                                                                        <TextInput
                                                                            id="password"
                                                                            ref="passwordInput"
                                                                            v-model="form_cuenta.password"
                                                                            :type="passwordFielType"
                                                                            class="mt-1 block w-full"
                                                                            autocomplete="new-password"
                                                                        />
                                                                    </div>
                                                                    <div class="flex justify-start ml-2">
                                                                        <v-tooltip text="Ver contraseña">
                                                                            <template v-slot:activator="{ props }">
                                                                                <v-btn v-bind="props" icon  @click="show_visibilty">
                                                                                    <v-icon>mdi-eye</v-icon>
                                                                                </v-btn>
                                                                            </template>
                                                                        </v-tooltip>
                                                                    </div>
                                                                </div>

                                                                <InputError :message="form_cuenta.errors.password" class="mt-2" />

                                                            </div>
                                                            <div>
                                                                <InputLabel for="docente" value="Docente o administrativo asociado a este perfil" />

                                                                <v-autocomplete variant="solo" :items="props.docente" item-title="nombre_completo" item-value="id"
                                                                                v-model="form_cuenta.docente_id"
                                                                >

                                                                </v-autocomplete>

                                                                <InputError class="mt-2" :message="form_cuenta.errors.docente_id" />
                                                            </div>
                                                            <div>
                                                                <InputLabel for="departamento" value="Departamento" />

                                                                <v-select variant="solo" :items="props.departamento" item-title="nameDepartamento" item-value="id"
                                                                          v-model="form_cuenta.departamento_id"
                                                                >

                                                                </v-select>

                                                                <InputError class="mt-2" :message="form_cuenta.errors.departamento_id" />
                                                            </div>
                                                            <div>
                                                                <InputLabel for="rol" value="Rol del usuario" />

                                                                <v-select variant="solo" :items="props.roles" item-title="name" item-value="id"
                                                                          v-model="form_cuenta.role"
                                                                >

                                                                </v-select>

                                                                <InputError class="mt-2" :message="form_cuenta.errors.role" />
                                                            </div>


                                                            <div class="flex justify-center items-center gap-4">
                                                                <PrimaryButton :disabled="form_cuenta.processing">Guardar</PrimaryButton>

                                                                <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
                                                                    <p v-if="form_cuenta.recentlySuccessful" class="text-sm text-gray-600">Guardado.</p>
                                                                </Transition>
                                                            </div>
                                                        </form>
<!--                                                        </section>-->
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </Modal>
                        </div>
                    </div>
                </div>
                <div class="mt-15 mb-4">
                    <strong class="text-lg text-gray-600">
                        Coordinación de formación docente y actualización
                        profesional.
                    </strong>
                </div>
                <TablaUsuariosCoordinacion
                    :users="props.users" :errors="props.errors"
                ></TablaUsuariosCoordinacion>
            </div>
<!--            -->

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <header>
                    <h2
                        class="text-xl font-medium text-gray-900 mb-8 text-center"
                    >
                        Nombre de la Institución, del Director y de la
                        Subdirección Académica
                    </h2>
                </header>
                <div class="grid grid-cols-1 md:grid-cols-2">
                    <div class="flex justify-center">
                        <tabla-sub
                            :sub="props.sub"
                            :modelValue="dialogSub"
                            @update:modelValue="dialogSub = $event"
                        ></tabla-sub>
                        <form-subdireccion
                            :sub="props.sub"
                            v-model="dialogSub"
                            @update:modelValue="dialogSub = $event"
                            :errors="props.errors"
                        ></form-subdireccion>
                        <template v-if="props.sub.length < 0">
                            <div
                                class="flex justify-end mt-8 mr-12 items-center"
                            >
                                <primary-button @click="dialogSub = true"
                                    >Crear/Guardar</primary-button
                                >
                            </div>
                        </template>
                    </div>

                    <div class="flex justify-center">
                        <tabla-director
                            :director="props.director"
                            :modelValue="dialogDirector"
                            @update:modelValue="dialogDirector = $event"
                        ></tabla-director>
                        <dialog-director
                            :director="props.director"
                            v-model="dialogDirector"
                            @update:modelValue="dialogDirector = $event"
                        ></dialog-director>
                        <template v-if="props.director.length < 0">
                            <div
                                class="flex justify-end mt-8 mr-12 items-center"
                            >
                                <primary-button @click="dialogDirector = true"
                                    >Crear/Guardar</primary-button
                                >
                            </div>
                        </template>
                    </div>
                </div>
                <div class="grid grid-cols-1">
                    <div class="flex justify-center">
                        <NombreInstitutoTable
                            :instituto="props.instituto"
                            :modelValue="dialogInstituto"
                            @update:modelValue="dialogInstituto = $event"
                        ></NombreInstitutoTable>
                        <DialogNombreInstituto
                            :instituto="props.instituto"
                            v-model="dialogInstituto"
                            @update:modelValue="dialogInstituto = $event"
                        ></DialogNombreInstituto>
                        <template v-if="props.instituto.length < 0">
                            <div
                                class="flex justify-end mt-8 mr-12 items-center"
                            >
                                <primary-button @click="dialogDirector = true"
                                    >Crear/Guardar</primary-button
                                >
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <header>
                    <h2 class="text-xl font-medium text-gray-900 mb-8">
                        Documentos
                    </h2>
                </header>

                <div class="mt-15 mt-16">
                    <strong class="text-lg text-gray-600">
                        Subir CVU editable.
                    </strong>
                </div>

                <form @submit.prevent="upload_cvu">
                    <div class="grid grid-cols-1">
                        <div class="flex justify-center">
                            <v-file-input
                                label=""
                                variant="solo"
                                @input="form_file.file = $event.target.files[0]"
                            ></v-file-input>
                            <div class="flex justify-end mt-2 ml-5 w-11">
                                <v-btn
                                    type="submit"
                                    color="blue-darken-1"
                                    width="500"
                                    icon="mdi-content-save-check-outline"
                                ></v-btn>
                            </div>
                        </div>
                    </div>
                </form>
                <header>
                    <h2 class="text-xl font-medium text-gray-900 mb-8 mt-8">
                        Membretado de documentos
                    </h2>
                </header>
                <div class="mt-3">
                    <div class="flex justify-start items-center">
                        <div class="grid grid-cols-1 md:grid-cols-2">
                            <div class="flex justify-start">
                                <strong class="text-lg text-gray-600">
                                    Subir membretado.
                                </strong>
                            </div>
                            <div class="flex justify-start ml-10 mb-2">
                                <v-tooltip location="bottom">
                                    <template v-slot:activator="{ props }">
                                        <v-btn
                                            icon
                                            v-bind="props"
                                            size="normal"
                                            color="blue-darken-1"
                                        >
                                            <v-icon> mdi-help </v-icon>
                                        </v-btn>
                                    </template>
                                    <span
                                    >La imagen debe tener la mejor calidad posible para que se vea bien al momento de la impresión.</span
                                    >
                                </v-tooltip>
                            </div>
                        </div>
                    </div>
                </div>
                <form @submit.prevent="upload_acta">
                    <div class="grid grid-cols-1">
                        <div class="flex justify-center">
                            <v-file-input
                                label="Acta Calificaciones"
                                variant="solo"
                                @input="
                                    form_file_acta_img.file =
                                        $event.target.files[0]
                                "
                            ></v-file-input>
                            <div class="flex justify-end mt-2 ml-5 w-11">
                                <v-btn
                                    type="submit"
                                    color="blue-darken-1"
                                    width="500"
                                    icon="mdi-content-save-check-outline"
                                ></v-btn>
                            </div>
                        </div>
                    </div>
                </form>
                <form @submit.prevent="upload_constancia">
                    <div class="grid grid-cols-1">
                        <div class="flex justify-center">
                            <v-file-input
                                label="Constancia"
                                variant="solo"
                                @input="
                                    form_file_constancia_img.file =
                                        $event.target.files[0]
                                "
                                ref="file_input_c"
                            ></v-file-input>
                            <div class="flex justify-end mt-2 ml-5 w-11">
                                <v-btn
                                    type="submit"
                                    color="blue-darken-1"
                                    width="500"
                                    icon="mdi-content-save-check-outline"
                                ></v-btn>
                            </div>
                        </div>
                    </div>
                </form>
<!--                <form @submit.prevent="upload_constancia_2">-->
<!--                    <div class="grid grid-cols-1">-->
<!--                        <div class="flex justify-center">-->
<!--                            <v-file-input-->
<!--                                label="Constancia imagen del reverso"-->
<!--                                variant="solo"-->
<!--                                @input="-->
<!--                                    form_file_constancia_img_2.file =-->
<!--                                        $event.target.files[0]-->
<!--                                "-->
<!--                            ></v-file-input>-->
<!--                            <div class="flex justify-end mt-2 ml-5 w-11">-->
<!--                                <v-btn-->
<!--                                    type="submit"-->
<!--                                    color="blue-darken-1"-->
<!--                                    width="500"-->
<!--                                    icon="mdi-content-save-check-outline"-->
<!--                                ></v-btn>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </form>-->
            </div>
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <header>
                    <h2 class="text-lg font-medium text-gray-900">
                        Imagenes del sistema
                    </h2>
                    <v-row dense justify="center" class="pa-6">
                        <v-col dense align="center" cols="6">
                            <div class="d-flex justify-start mb-5">
                                <v-tooltip location="right">
                                    <template v-slot:activator="{ props }">
                                        <v-btn
                                            icon
                                            color="blue-darken-1"
                                            v-bind="props"
                                            size="normal"
                                        >
                                            <v-icon> mdi-help </v-icon>
                                        </v-btn>
                                    </template>
                                    <span>Los archivos deben ser JPG</span>
                                </v-tooltip>
                            </div>
                            <div
                                class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                            >
                                <div class="grid grid-cols-1">
                                    <div class="flex justify-center">
                                        <v-file-input
                                            label="Ingresar logo del Instituto Tecnológico"
                                            variant="solo"
                                            @input="
                                                img_logo_ittg.file =
                                                    $event.target.files[0]
                                            "
                                        ></v-file-input>
                                        <div
                                            class="flex justify-end mt-2 ml-5 w-11"
                                        >
                                            <v-btn
                                                @click="upload_logotec"
                                                type="submit"
                                                color="blue-darken-1"
                                                width="500"
                                                icon="mdi-content-save-check-outline"
                                            ></v-btn>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1">
                                    <div class="flex justify-center">
                                        <v-file-input
                                            label="Ingresar logo del Tecnm"
                                            variant="solo"
                                            @input="
                                                img_logo_tecnm.file =
                                                    $event.target.files[0]
                                            "
                                        ></v-file-input>
                                        <div
                                            class="flex justify-end mt-2 ml-5 w-11"
                                        >
                                            <v-btn
                                                @click="upload_tecnm"
                                                color="blue-darken-1"
                                                width="500"
                                                icon="mdi-content-save-check-outline"
                                            ></v-btn>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1">
                                    <div class="flex justify-center">
                                        <v-file-input
                                            label="Ingresar logo de la SEP"
                                            variant="solo"
                                            @input="
                                                img_logo_educacion.file =
                                                    $event.target.files[0]
                                            "
                                        ></v-file-input>
                                        <div
                                            class="flex justify-end mt-2 ml-5 w-11"
                                        >
                                            <v-btn
                                                @click="upload_educacion"
                                                color="blue-darken-1"
                                                width="500"
                                                icon="mdi-content-save-check-outline"
                                            ></v-btn>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </v-col>
                    </v-row>
                </header>
            </div>
<!--            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">-->
<!--                <header>-->
<!--                    <h2 class="text-lg font-medium text-gray-900">-->
<!--                        Subir imagenes (iconos,etc)-->
<!--                    </h2>-->
<!--                </header>-->
<!--            </div>-->
        </div>

        <CustomSnackBar
            :message="message"
            :color="color"
            :timeout="timeout"
            v-model="snackbar"
            @update:modelValue="snackbar = $event"
        ></CustomSnackBar>
    </AuthenticatedLayout>
</template>

<style scoped></style>
