# BeCreative Wedding Sites

Sistema reutilizable para gestionar webs de boda dentro del WordPress actual de Be Creative Weddings.

## Arquitectura

- Una sola instalación de WordPress.
- Un solo plugin.
- Varias bodas independientes.
- Plantillas visuales reutilizables.
- Datos, RSVP, invitación y acceso de pareja configurables por boda.

## Incluido en esta versión

### Bodas
- Tipo de contenido **Bodas** dentro de WordPress.
- Nombres, fecha, lugar, destino, frase principal y cierre.
- Imagen de portada e imagen final.
- Plantilla seleccionable.

### Secciones opcionales
Cada boda puede activar o desactivar:
- Nuestra historia
- La boda
- Destino / Nuestra Menorca
- Información
- RSVP

### Historia
Bloques repetibles con:
- Año / fecha
- Título
- Texto
- Tratamiento visual estándar, destacado o collage
- Hasta tres imágenes por capítulo

### La boda
Bloques repetibles con:
- Momento
- Espacio
- Hora opcional
- Texto
- Imagen

### Destino / Nuestra Menorca
Bloques repetibles con:
- Categoría
- Lugar
- Texto personal
- Imagen
- Enlace opcional

### Información
Bloques libres para transporte, alojamiento, vuelos, ferry, parking, niños, calzado, dress code y cualquier otra información necesaria.

### RSVP
- Guardado real en una tabla propia de WordPress.
- Campos configurables por boda.
- Asistencia, acompañantes, niños, transporte, llegada, salida, alergias/intolerancias y necesidades especiales.
- Resumen global para Be Creative.
- Exportación CSV.

### Panel privado de la pareja
- Acceso mediante usuario propio.
- Sin acceso a la administración de WordPress.
- Resumen de confirmaciones.
- Listado de invitados.
- Exportación CSV.

### Invitación digital
Funciones opcionales por boda:
- Activar invitación digital.
- Imagen de invitación para compartir.
- Imagen específica para previsualización de enlace.
- Portada de entrada opcional antes de la web.

### Plantilla Minimal Editorial
Experiencia multipágina:
- Inicio
- Historia
- La boda
- Menorca
- Información
- RSVP

## Principios técnicos

- No duplicar instalaciones de WordPress.
- No duplicar páginas Elementor por pareja.
- No modificar las páginas corporativas actuales.
- No añadir dependencias innecesarias.
- Optimizar las imágenes antes de alojarlas.
- Mantener el código versionado en GitHub.
- Probar la versión de plugin antes de activarla en producción.

## Estado

Versión 0.3.x de desarrollo. Preparada para la primera prueba controlada antes de instalarla en la web de producción.
