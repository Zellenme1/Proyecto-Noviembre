-- ==============================================================================
-- DATOS DE PRUEBA (se ejecutan tras el esquema: 02-seeds.sql)
--
-- CREDENCIALES DE PRUEBA
--   Admin:        admin@trueques.local        / Admin1234!
--   Profesionales (todos): laura@..., carlos@..., marta@..., javier@..., ana@...
--                 @trueques.local             / Trueque1234!
-- Las contraseñas están guardadas como hash bcrypt (password_hash de PHP).
-- ==============================================================================
SET NAMES utf8mb4;
USE trueques_db;

-- ---------------------------------------------------------------- USUARIOS
-- Saldos coherentes con las solicitudes COMPLETADAS de más abajo
-- (50 de bienvenida + puntos ganados - puntos gastados). La suma total es 250.
INSERT INTO usuarios (id_usuario, nombre, apellidos, email, password, telefono, rol, puntos_saldo, fecha_registro) VALUES
(1, 'Admin', 'Plataforma', 'admin@trueques.local',  '$2y$10$BRcm/4l1MTql8knudG3AC.D9mZm5lnxcIZ3pO.crn4C6y.xbb/t2W', '600000000', 'ADMIN',       0,  '2026-09-01 09:00:00'),
(2, 'Laura',  'Gómez Ruiz',       'laura@trueques.local',  '$2y$10$jffo5kO6QP4AdaemjQwnv.VoHCC4aaraUQbg4R9QEEUMOO5H9VMS2', '600111111', 'PROFESIONAL', 44, '2026-09-05 10:00:00'),
(3, 'Carlos', 'Martínez Pérez',   'carlos@trueques.local', '$2y$10$jffo5kO6QP4AdaemjQwnv.VoHCC4aaraUQbg4R9QEEUMOO5H9VMS2', '600222222', 'PROFESIONAL', 80, '2026-09-06 11:30:00'),
(4, 'Marta',  'Sánchez López',    'marta@trueques.local',  '$2y$10$jffo5kO6QP4AdaemjQwnv.VoHCC4aaraUQbg4R9QEEUMOO5H9VMS2', '600333333', 'PROFESIONAL', 38, '2026-09-08 16:15:00'),
(5, 'Javier', 'Fernández Díaz',   'javier@trueques.local', '$2y$10$jffo5kO6QP4AdaemjQwnv.VoHCC4aaraUQbg4R9QEEUMOO5H9VMS2', '600444444', 'PROFESIONAL', 50, '2026-09-10 12:00:00'),
(6, 'Ana',    'Torres Navarro',   'ana@trueques.local',    '$2y$10$jffo5kO6QP4AdaemjQwnv.VoHCC4aaraUQbg4R9QEEUMOO5H9VMS2', '600555555', 'PROFESIONAL', 38, '2026-09-12 18:45:00');

-- -------------------------------------------------------------- CATEGORIAS
INSERT INTO categorias (id_categoria, nombre, descripcion) VALUES
(1, 'Hogar y reparaciones',   'Fontanería, electricidad, pintura y pequeñas obras.'),
(2, 'Informática y tecnología','Soporte técnico, redes y desarrollo web.'),
(3, 'Educación y clases',     'Clases particulares y refuerzo escolar.'),
(4, 'Bienestar y salud',      'Fisioterapia, masajes y cuidado personal.');

-- --------------------------------------------------------------- SERVICIOS
INSERT INTO servicios (id_servicio, id_categoria, nombre, descripcion, puntos_hora_referencia) VALUES
(1, 1, 'Reparación de tuberías',    'Arreglo de fugas, grifos y desatascos.',          12),
(2, 1, 'Instalación eléctrica',     'Enchufes, luminarias y cuadros eléctricos.',      14),
(3, 1, 'Pintura de interiores',     'Pintado de paredes y techos.',                    10),
(4, 2, 'Reparación de ordenadores', 'Diagnóstico, limpieza e instalación de sistemas.',12),
(5, 2, 'Desarrollo de páginas web', 'Webs a medida con HTML, CSS, JS y PHP.',          15),
(6, 3, 'Clases de matemáticas',     'ESO y Bachillerato.',                             10),
(7, 3, 'Clases de inglés',          'Conversación y preparación de exámenes.',         10),
(8, 4, 'Fisioterapia y masajes',    'Masaje descontracturante y recuperación.',        14);

-- ---------------------------------------------------- OFERTAS PROFESIONALES
-- Tarifas entre el 50 % y el 150 % de la referencia. La oferta 10 está inactiva.
INSERT INTO ofertas_profesionales (id_oferta, id_profesional, id_servicio, puntos_hora, descripcion_personal, activo) VALUES
(1,  2, 1, 12, 'Fontanera con 10 años de experiencia en viviendas.',          TRUE),
(2,  2, 3,  9, 'Pintura cuidada y limpia, aporto el material básico.',        TRUE),
(3,  3, 2, 15, 'Electricista autorizado, instalaciones y revisiones.',        TRUE),
(4,  3, 1, 11, 'Reparaciones sencillas de fontanería.',                       TRUE),
(5,  4, 4, 12, 'Técnica informática: Windows, Linux y redes domésticas.',     TRUE),
(6,  4, 5, 18, 'Desarrolladora web full stack.',                              TRUE),
(7,  5, 6, 10, 'Profesor de matemáticas de secundaria.',                      TRUE),
(8,  5, 7,  8, 'Clases de inglés nivel B2, conversación.',                    TRUE),
(9,  6, 8, 14, 'Fisioterapeuta colegiada, masaje deportivo.',                 TRUE),
(10, 6, 7, 10, 'Clases de inglés (temporalmente no disponible).',             FALSE);

-- ----------------------------------------------------- SOLICITUDES TRUEQUE
-- puntos_totales = puntos_hora de la oferta x horas_estimadas.
-- Hay una solicitud en cada estado para probar la máquina de estados.
INSERT INTO solicitudes_trueque
(id_solicitud, id_cliente, id_oferta, horas_estimadas, puntos_totales, fecha_propuesta, estado, confirmado_cliente, confirmado_prestador, fecha_creacion) VALUES
(1, 4, 1, 2.00, 24, '2026-10-20 10:00:00', 'COMPLETADO',   TRUE,  TRUE,  '2026-10-15 09:00:00'),
(2, 5, 3, 2.00, 30, '2026-10-25 16:00:00', 'COMPLETADO',   TRUE,  TRUE,  '2026-10-20 11:00:00'),
(3, 6, 5, 1.00, 12, '2026-11-02 18:00:00', 'COMPLETADO',   TRUE,  TRUE,  '2026-10-28 17:30:00'),
(4, 2, 7, 3.00, 30, '2026-11-04 17:00:00', 'COMPLETADO',   TRUE,  TRUE,  '2026-10-30 08:20:00'),
(5, 3, 6, 2.00, 36, '2026-11-08 17:00:00', 'EN_EJECUCION', TRUE,  FALSE, '2026-11-03 12:00:00'),
(6, 5, 9, 1.00, 14, '2026-11-14 12:00:00', 'ACEPTADO',     FALSE, FALSE, '2026-11-08 10:10:00'),
(7, 6, 2, 4.00, 36, '2026-11-20 09:00:00', 'SOLICITADO',   FALSE, FALSE, '2026-11-09 19:00:00'),
(8, 4, 8, 2.00, 16, '2026-11-12 18:00:00', 'RECHAZADO',    FALSE, FALSE, '2026-11-06 14:00:00'),
(9, 2, 5, 1.50, 18, '2026-11-11 10:00:00', 'CANCELADO',    FALSE, FALSE, '2026-11-05 09:45:00');

-- ------------------------------------------------------------- VALORACIONES
-- La solicitud 4 (Javier) queda sin valorar a propósito, y Javier no tiene
-- ninguna valoración: sirve para probar el LEFT JOIN del buscador.
INSERT INTO valoraciones (id_valoracion, id_solicitud, puntuacion, comentario, fecha_valoracion) VALUES
(1, 1, 5, 'Muy rápida y profesional, solucionó la fuga en una hora.', '2026-10-21 12:00:00'),
(2, 2, 4, 'Buen trabajo, aunque llegó con algo de retraso.',           '2026-10-26 09:30:00'),
(3, 3, 5, 'Dejó el ordenador como nuevo y me explicó todo.',           '2026-11-03 10:15:00');