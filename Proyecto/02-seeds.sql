SET NAMES utf8mb4;
USE trueques_db;

-- USUARIOS. Saldos coherentes con las solicitudes COMPLETADAS:
-- 50 de bienvenida +/- puntos de los trueques. La suma de profesionales es 150.
INSERT INTO usuarios (id_usuario, nombre, apellidos, email, password, telefono, rol, puntos_saldo) VALUES
(1, 'Admin',  'Plataforma',     'admin@trueques.local',  '$2y$10$BRcm/4l1MTql8knudG3AC.D9mZm5lnxcIZ3pO.crn4C6y.xbb/t2W', '600000000', 'ADMIN',       0),
(2, 'Laura',  'Gómez Ruiz',     'laura@trueques.local',  '$2y$10$jffo5kO6QP4AdaemjQwnv.VoHCC4aaraUQbg4R9QEEUMOO5H9VMS2', '600111111', 'PROFESIONAL', 62),
(3, 'Carlos', 'Martínez Pérez', 'carlos@trueques.local', '$2y$10$jffo5kO6QP4AdaemjQwnv.VoHCC4aaraUQbg4R9QEEUMOO5H9VMS2', '600222222', 'PROFESIONAL', 50),
(4, 'Marta',  'Sánchez López',  'marta@trueques.local',  '$2y$10$jffo5kO6QP4AdaemjQwnv.VoHCC4aaraUQbg4R9QEEUMOO5H9VMS2', '600333333', 'PROFESIONAL', 38);

INSERT INTO categorias (id_categoria, nombre, descripcion) VALUES
(1, 'Hogar y reparaciones',     'Fontanería, electricidad y pequeñas obras.'),
(2, 'Informática y tecnología', 'Soporte técnico y desarrollo web.');

INSERT INTO servicios (id_servicio, id_categoria, nombre, descripcion, puntos_hora_referencia) VALUES
(1, 1, 'Reparación de tuberías',    'Arreglo de fugas, grifos y desatascos.', 12),
(2, 1, 'Instalación eléctrica',     'Enchufes, luminarias y cuadros.',        14),
(3, 2, 'Reparación de ordenadores', 'Diagnóstico, limpieza e instalación.',   12);

INSERT INTO ofertas_profesionales (id_oferta, id_profesional, id_servicio, puntos_hora, descripcion_personal, activo) VALUES
(1, 2, 1, 12, 'Fontanera con 10 años de experiencia.', TRUE),
(2, 3, 2, 15, 'Electricista autorizado.',              TRUE),
(3, 4, 3, 12, 'Técnica informática: Windows y Linux.', TRUE);

-- SOLICITUDES: puntos_totales = puntos_hora de la oferta x horas_estimadas.
-- Cubren: COMPLETADO (x2), SOLICITADO, ACEPTADO y EN_EJECUCION (solo confirmó el cliente).
INSERT INTO solicitudes_trueque
(id_solicitud, id_cliente, id_oferta, horas_estimadas, puntos_totales, fecha_propuesta, estado, confirmado_cliente, confirmado_prestador) VALUES
(1, 4, 1, 2.00, 24, '2026-10-20 10:00:00', 'COMPLETADO',   TRUE,  TRUE),
(2, 2, 3, 1.00, 12, '2026-10-25 16:00:00', 'COMPLETADO',   TRUE,  TRUE),
(3, 3, 3, 2.00, 24, '2026-11-20 09:00:00', 'SOLICITADO',   FALSE, FALSE),
(4, 4, 2, 1.00, 15, '2026-11-18 12:00:00', 'ACEPTADO',     FALSE, FALSE),
(5, 2, 2, 1.00, 15, '2026-11-12 17:00:00', 'EN_EJECUCION', TRUE,  FALSE);

-- VALORACIÓN: solo la solicitud 1. La 2 queda completada y SIN valorar a propósito.
INSERT INTO valoraciones (id_valoracion, id_solicitud, puntuacion, comentario) VALUES
(1, 1, 5, 'Muy rápida y profesional, solucionó la fuga en una hora.');



-- Rol	Email	Contraseña
-- Administrador	admin@trueques.local	Admin1234!
-- Profesional (Laura)	laura@trueques.local	Trueque1234!
-- Profesional (Carlos)	carlos@trueques.local	Trueque1234!
-- Profesional (Marta)	marta@trueques.local	Trueque1234!