// Validaciones de cliente reutilizables para los formularios de AW_Bistro.
// El servidor sigue siendo la red de seguridad: este archivo solo evita
// envíos inválidos obvios y da feedback inmediato al usuario.

(function (global) {
    'use strict';

    // ── Helpers de validación ──────────────────────────────────────────────

    function esVacio(valor) {
        return valor === null || valor === undefined || String(valor).trim() === '';
    }

    function esEmailValido(valor) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(valor).trim());
    }

    function tieneLongitudMin(valor, n) {
        return String(valor).trim().length >= n;
    }

    function tieneLongitudMax(valor, n) {
        return String(valor).trim().length <= n;
    }

    function fortalezaPassword(valor) {
        var v = String(valor);
        var puntos = 0;
        if (v.length >= 6)        puntos++;
        if (v.length >= 10)       puntos++;
        if (/[A-Z]/.test(v))      puntos++;
        if (/[0-9]/.test(v))      puntos++;
        if (/[^A-Za-z0-9]/.test(v)) puntos++;
        if (puntos <= 2) return { nivel: 'débil',  mensaje: 'Contraseña débil' };
        if (puntos <= 4) return { nivel: 'media',  mensaje: 'Contraseña aceptable' };
        return { nivel: 'fuerte', mensaje: 'Contraseña fuerte' };
    }

    function esEnteroEntre(valor, min, max) {
        var s = String(valor).trim();
        if (!/^-?\d+$/.test(s)) return false;
        var n = parseInt(s, 10);
        return n >= min && n <= max;
    }

    function esNumeroPositivo(valor) {
        var n = parseFloat(String(valor).replace(',', '.'));
        return !isNaN(n) && n > 0;
    }

    function archivoEsImagenValida(file, maxMB) {
        if (!file) return false;
        if (!/^image\//.test(file.type)) return false;
        if (file.size > maxMB * 1024 * 1024) return false;
        return true;
    }

    function totalArchivosValido(filesList, maxN, maxMBPorArchivo) {
        if (!filesList) return true;
        if (filesList.length > maxN) return false;
        for (var i = 0; i < filesList.length; i++) {
            if (!archivoEsImagenValida(filesList[i], maxMBPorArchivo)) return false;
        }
        return true;
    }

    // ── Helpers de UI ──────────────────────────────────────────────────────

    function spanError(input) {
        // Reutiliza el siguiente <span class="error-js"> si ya existe; si no, lo crea.
        var sib = input.nextElementSibling;
        while (sib && sib.classList && sib.classList.contains('error')) sib = sib.nextElementSibling;
        if (sib && sib.classList && sib.classList.contains('error-js')) return sib;
        var span = document.createElement('span');
        span.className = 'error-js';
        input.parentNode.insertBefore(span, input.nextSibling);
        return span;
    }

    function mostrarErrorCampo(input, mensaje) {
        var s = spanError(input);
        s.textContent = mensaje;
        s.style.display = '';
        input.classList.add('campo-invalido');
    }

    function limpiarErrorCampo(input) {
        var sib = input.nextElementSibling;
        while (sib) {
            if (sib.classList && sib.classList.contains('error-js')) {
                sib.textContent = '';
                sib.style.display = 'none';
                break;
            }
            sib = sib.nextElementSibling;
        }
        input.classList.remove('campo-invalido');
    }

    function previsualizarImagen(inputFile, contenedor) {
        if (!inputFile || !contenedor) return;
        inputFile.addEventListener('change', function () {
            contenedor.innerHTML = '';
            var f = inputFile.files && inputFile.files[0];
            if (!f || !/^image\//.test(f.type)) return;
            var img = document.createElement('img');
            img.src = URL.createObjectURL(f);
            img.style.maxWidth = '120px';
            img.style.maxHeight = '120px';
            img.style.marginTop = '8px';
            img.onload = function () { URL.revokeObjectURL(img.src); };
            contenedor.appendChild(img);
        });
    }

    function previsualizarImagenes(inputFile, contenedor) {
        if (!inputFile || !contenedor) return;
        inputFile.addEventListener('change', function () {
            contenedor.innerHTML = '';
            var files = inputFile.files || [];
            for (var i = 0; i < files.length; i++) {
                var f = files[i];
                if (!/^image\//.test(f.type)) continue;
                var img = document.createElement('img');
                img.src = URL.createObjectURL(f);
                img.style.maxWidth = '90px';
                img.style.maxHeight = '90px';
                img.style.margin = '4px';
                img.onload = (function (src) { return function () { URL.revokeObjectURL(src); }; })(img.src);
                contenedor.appendChild(img);
            }
        });
    }

    // ── Aplicador de reglas ────────────────────────────────────────────────
    // Una regla es una cadena ('requerido', 'email') o un array (['minLen', 3]).

    function aplicaRegla(form, input, regla) {
        var nombre = (typeof regla === 'string') ? regla : regla[0];
        var valor = (input.type === 'file') ? input.files : input.value;

        switch (nombre) {
            case 'requerido':
                if (esVacio(valor) && (!input.files || input.files.length === 0)) {
                    return 'Este campo es obligatorio.';
                }
                return null;
            case 'email':
                if (!esVacio(valor) && !esEmailValido(valor)) {
                    return 'Introduce un email válido.';
                }
                return null;
            case 'minLen':
                if (!esVacio(valor) && !tieneLongitudMin(valor, regla[1])) {
                    return 'Debe tener al menos ' + regla[1] + ' caracteres.';
                }
                return null;
            case 'maxLen':
                if (!esVacio(valor) && !tieneLongitudMax(valor, regla[1])) {
                    return 'No puede superar los ' + regla[1] + ' caracteres.';
                }
                return null;
            case 'coincideCon':
                var otro = form.elements[regla[1]];
                if (otro && valor !== otro.value) {
                    return 'Los valores no coinciden.';
                }
                return null;
            case 'enteroEntre':
                if (!esVacio(valor) && !esEnteroEntre(valor, regla[1], regla[2])) {
                    return 'Debe ser un entero entre ' + regla[1] + ' y ' + regla[2] + '.';
                }
                return null;
            case 'numeroPositivo':
                if (!esVacio(valor) && !esNumeroPositivo(valor)) {
                    return 'Debe ser un número mayor que 0.';
                }
                return null;
            case 'imagen':
                var maxMB = regla[1] || 2;
                if (input.files && input.files.length > 0) {
                    var f = input.files[0];
                    if (!/^image\//.test(f.type)) {
                        return 'El archivo no es una imagen válida.';
                    }
                    if (f.size > maxMB * 1024 * 1024) {
                        return 'La imagen supera el tamaño máximo de ' + maxMB + ' MB.';
                    }
                }
                return null;
            case 'imagenes':
                var maxN = regla[1] || 5;
                var maxMB2 = regla[2] || 2;
                if (input.files && input.files.length > 0) {
                    if (input.files.length > maxN) {
                        return 'Sube como máximo ' + maxN + ' imágenes.';
                    }
                    for (var k = 0; k < input.files.length; k++) {
                        var fk = input.files[k];
                        if (!/^image\//.test(fk.type)) {
                            return 'El archivo «' + fk.name + '» no es una imagen válida.';
                        }
                        if (fk.size > maxMB2 * 1024 * 1024) {
                            return 'La imagen «' + fk.name + '» supera ' + maxMB2 + ' MB.';
                        }
                    }
                }
                return null;
            default:
                return null;
        }
    }

    function validaCampo(form, input, reglas) {
        for (var i = 0; i < reglas.length; i++) {
            var error = aplicaRegla(form, input, reglas[i]);
            if (error) {
                mostrarErrorCampo(input, error);
                return false;
            }
        }
        limpiarErrorCampo(input);
        return true;
    }

    function validarFormulario(form, reglas) {
        var ok = true;
        for (var nombreCampo in reglas) {
            if (!Object.prototype.hasOwnProperty.call(reglas, nombreCampo)) continue;
            var input = form.elements[nombreCampo];
            if (!input) continue;
            if (!validaCampo(form, input, reglas[nombreCampo])) ok = false;
        }
        return ok;
    }

    function activarValidacion(formId, reglas) {
        var form = (typeof formId === 'string') ? document.getElementById(formId) : formId;
        if (!form) return;
        form.addEventListener('submit', function (e) {
            if (!validarFormulario(form, reglas)) {
                e.preventDefault();
            }
        });
        for (var nombreCampo in reglas) {
            if (!Object.prototype.hasOwnProperty.call(reglas, nombreCampo)) continue;
            var input = form.elements[nombreCampo];
            if (!input) continue;
            (function (inp, regs) {
                inp.addEventListener('blur', function () { validaCampo(form, inp, regs); });
            })(input, reglas[nombreCampo]);
        }
    }

    // ── Exposición pública ─────────────────────────────────────────────────
    global.Validaciones = {
        esEmailValido: esEmailValido,
        tieneLongitudMin: tieneLongitudMin,
        tieneLongitudMax: tieneLongitudMax,
        fortalezaPassword: fortalezaPassword,
        esEnteroEntre: esEnteroEntre,
        esNumeroPositivo: esNumeroPositivo,
        archivoEsImagenValida: archivoEsImagenValida,
        totalArchivosValido: totalArchivosValido,
        mostrarErrorCampo: mostrarErrorCampo,
        limpiarErrorCampo: limpiarErrorCampo,
        previsualizarImagen: previsualizarImagen,
        previsualizarImagenes: previsualizarImagenes,
        validarFormulario: validarFormulario,
        activarValidacion: activarValidacion
    };

    // Aliases globales cómodos para usar desde los <script> inline.
    global.activarValidacion = activarValidacion;
    global.validarFormulario = validarFormulario;
    global.mostrarErrorCampo = mostrarErrorCampo;
    global.limpiarErrorCampo = limpiarErrorCampo;
    global.previsualizarImagen = previsualizarImagen;
    global.previsualizarImagenes = previsualizarImagenes;
    global.fortalezaPassword = fortalezaPassword;

})(window);
