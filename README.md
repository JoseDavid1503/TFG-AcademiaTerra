# 🎓 Academia Terra

Plataforma integral de gestión educativa y entorno virtual de aprendizaje orientada a ciclos formativos de la familia de Informática y Comunicaciones (ASIR, DAW y SMR). 

Este proyecto ha sido desarrollado como Trabajo de Fin de Grado (TFG) para el Ciclo Formativo de Grado Superior en **Administración de Sistemas Informáticos en Red (ASIR)** por Jose David Chica Gordo.

## ⚙️ Pila Tecnológica (Stack)

El sistema está diseñado bajo una arquitectura modular y escalable, utilizando tecnologías de código abierto (*Open Source*):

* **Infraestructura:** Pila LAMP (Linux/Debian, Apache, MariaDB).
* **Backend:** PHP 8 (Abstracción de datos mediante PDO y sentencias preparadas).
* **Frontend:** HTML5 semántico, maquetación adaptativa con Tailwind CSS.
* **Interactividad y Gráficos:** JavaScript (ES6+), SweetAlert2 y Chart.js.
* **Generación de Reportes:** Dompdf (vía Composer).

## 🚀 Funcionalidades Principales

* **Control de Acceso Basado en Roles (RBAC):** Interfaces y permisos diferenciados para Administradores, Profesores y Alumnos.
* **Aula Virtual Segura:** Sistema de automatrícula blindado contra duplicidades, entregas de actividades documentales y foros de debate.
* **Cuadro de Mando (Dashboard):** Panel analítico interactivo con estadísticas de matriculación y rendimiento en tiempo real.
* **Integridad Relacional:** Base de datos estructurada con motor InnoDB, garantizando la consistencia mediante eliminaciones en cascada.
* **Exportación de Actas:** Generación automatizada de informes oficiales en formato PDF y exportación de datos en CSV.

## 🛡️ Seguridad y Bastionado

* Mitigación de inyecciones SQL mediante el uso estricto de PDO.
* Prevención de ataques XSS (*Cross-Site Scripting*) en la renderización de formularios.
* Bastionado de directorios sensibles mediante directivas `.htaccess` en Apache.
* Cifrado de contraseñas de usuario en base de datos.
* Uso de variables de entorno e ignorado de credenciales sensibles.

---
*Desarrollado para fines académicos - 2026*
