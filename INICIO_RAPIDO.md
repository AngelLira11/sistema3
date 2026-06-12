# 🚀 Inicio Rápido - Registro en Dos Etapas

## En 5 Minutos

### Paso 1: Verificar archivos (1 min)
Asegurate que estos archivos existan en la raíz (`d:\sistema3`):
- ✅ `profile_check.php` (NUEVO)
- ✅ `complete_profile.php` (NUEVO)
- ✅ `complete_profile_process.php` (NUEVO)

### Paso 2: Probar nuevo registro (3 min)

1. Abre tu navegador
2. Ve a: `http://localhost/sistema3/registro.php`
3. Deberías ver SOLO 3 campos:
   - Email
   - Contraseña
   - Confirmar contraseña

4. Completa:
   ```
   Email: testuser@example.com
   Contraseña: Password123
   Confirmar: Password123
   ```

5. Clic en "Continuar a Etapa 2"

### Paso 3: Completar perfil (1 min)

Serás redirigido automáticamente a `complete_profile.php`

Completa los datos:
```
Nombre: Juan Pérez
No. Control: 21130001
Carrera: ING. SISTEMAS COMPUTACIONALES
Opción: Informe de Residencia Profesional
Email: testuser@example.com
Celular: 8711234567
Fecha: 2026-05-28
Graduación: Graduación 1 (Mar-Abr)
```

Clic en "Finalizar Registro"

## ✅ ¿Funciona?

Si ves:
- ✅ Alerta verde de éxito
- ✅ Dashboard con tus datos completos
- ✅ Puedes hacer clic en "Generar Constancia"

**¡EXCELENTE! ✨ El sistema funciona correctamente.**

## 🔄 Flujos Principales

### Flujo 1: Registro Nuevo
```
1. registro.php (email + password)
   ↓
2. complete_profile.php (datos personales)
   ↓
3. dashboard.php (acceso completo)
```

### Flujo 2: Login (perfil completo)
```
1. index.php (login)
   ↓
2. Verificar credenciales
   ↓
3. ¿Perfil completo? SÍ
   ↓
4. dashboard.php (acceso directo)
```

### Flujo 3: Login (perfil incompleto)
```
1. index.php (login)
   ↓
2. Verificar credenciales
   ↓
3. ¿Perfil completo? NO
   ↓
4. complete_profile.php (completar datos)
   ↓
5. dashboard.php (acceso después)
```

## 🛑 Si algo no funciona

### Error: "archivo no encontrado"
✅ Verifica que `profile_check.php` está en la raíz

### Error: "Redireccionamiento infinito"
✅ Verifica que `session_start()` está al inicio de cada archivo

### Error: "No aparece complete_profile.php"
✅ Verifica la ruta en la redirección es correcta

## 📋 Cambios Principales

| Antes | Ahora |
|-------|-------|
| 1 formulario con todos los campos | 2 formularios (rápido + completo) |
| Validación de todo a la vez | Validación por etapas |
| Posible abandono en registro | Registro más simple |
| Perfectil incompleto = error | Perfil incompleto = redirección automática |
| Sin generación de PDF incompleta | Prevención automática |

## 🎯 Lo que cambia para el usuario

### Registro (Nuevo)
```
ANTES: formulario largo y complejo
AHORA: formulario corto (2 minutos) + completar después
```

### Login (Mejorado)
```
ANTES: acceso directo si datos existen
AHORA: si faltan datos → completa automáticamente
```

### Generación de PDF (Segura)
```
ANTES: error si faltan datos
AHORA: redirige a completar datos primero
```

## 📚 Documentación Completa

Para más detalles, lee:
- `FLUJO_REGISTRO_DOS_ETAPAS.md` (documentación completa)
- `CAMBIOS_POR_ARCHIVO.md` (qué cambió en cada archivo)
- `GUIA_PRUEBAS.md` (guía de pruebas detallada)

## 💾 Base de Datos

**✅ SIN CAMBIOS**

Se usan exactamente los mismos campos que antes.
No requiere migraciones.

## 🎉 Listo

¡Tu sistema de registro en dos etapas está funcionando!

**Próximos pasos**:
1. Probar el flujo completo (5 min)
2. Revisar la documentación (10 min)
3. Realizar las pruebas (15 min)
4. Implementar en producción ✨

---

*Guía rápida v1.0*
