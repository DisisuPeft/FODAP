import Swal from "sweetalert2";
export function notify(titulo,icono,mensaje){
    Swal.fire({
        title: titulo,
        text: mensaje,
        icon: icono
    })
}

export function AlertLoading(titulo,mensaje){
    Swal.fire({
        title: titulo,
        text: mensaje,
        allowOutsideClick: false,
        imageUrl: 'assets/images/cloud-upload.gif',
        allowEscapeKey: false,
        allowEnterKey:false,
        showConfirmButton: false,
        showDenyButton: false,
        showCancelButton: false,
        imageWidth: 300
    })
}

export function success_alert(titulo, mensaje){
    Swal.fire({
        title: titulo,
        text: mensaje,
        allowOutsideClick: false,
        icon: 'success',
        timer: 3000,
        allowEscapeKey: false,
        allowEnterKey:false,
        showConfirmButton: false,
        showDenyButton: false,
        showCancelButton: false
    })
}

export function errorMsg(titulo,mensaje){
    Swal.fire({
        title: titulo,
        text: mensaje,
        allowOutsideClick: false,
        icon: 'error',
        // timer: 3000,
        allowEscapeKey: false,
        allowEnterKey:false,
        showConfirmButton: true,
        showDenyButton: false,
        showCancelButton: false,
        confirmButtonText: 'Ok',
    })
}

export function confirmy(titulo,icono,mensaje,textBtnOk,textBtnCancel,funcionOk,paramsOk='',funcionCancel=null,paramsCancel=''){
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success me-2',
            cancelButton: 'btn btn-danger me-2'
        },
        buttonsStyling: true,
    })

    swalWithBootstrapButtons.fire({
        title: titulo,
        text: mensaje,
        icon: icono,
        showCancelButton: true,
        confirmButtonText: textBtnOk,
        cancelButtonText: textBtnCancel,
        allowOutsideClick: false,
        reverseButtons: true
    }).then((result) => {
        if (result.value) {
            window[funcionOk](paramsOk);
        } else if (result.dismiss === Swal.DismissReason.cancel){
            if(funcionCancel!=null)
                window[funcionCancel](paramsCancel);
        }
    })
}
