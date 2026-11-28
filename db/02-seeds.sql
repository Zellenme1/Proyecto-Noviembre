SET NAMES utf8mb4;
USE trueques_db;

-- USUARIOS. Saldos coherentes con las solicitudes COMPLETADAS:
-- 50 de bienvenida +/- puntos de los trueques completados.
INSERT INTO usuarios (
    id_usuario, nombre, apellidos, email, password, telefono, rol, puntos_saldo, foto, curriculum
) VALUES
(1, 'Admin',  'Plataforma',     'admin@trueques.local',  '$2y$10$BRcm/4l1MTql8knudG3AC.D9mZm5lnxcIZ3pO.crn4C6y.xbb/t2W', '600000000', 'ADMIN',       0, '/uploads/avatars/images.jpg',      '/uploads/curriculum/cv-demo-1.pdf'),
(2, 'Laura',  'Gomez Ruiz',     'laura@trueques.local',  '$2y$10$jffo5kO6QP4AdaemjQwnv.VoHCC4aaraUQbg4R9QEEUMOO5H9VMS2', '600111111', 'PROFESIONAL', 15, '/uploads/avatars/images (2).jpg',  '/uploads/curriculum/cv-demo-2.pdf'),
(3, 'Carlos', 'Martinez Perez', 'carlos@trueques.local', '$2y$10$jffo5kO6QP4AdaemjQwnv.VoHCC4aaraUQbg4R9QEEUMOO5H9VMS2', '600222222', 'PROFESIONAL', 54, '/uploads/avatars/images (3).jpg',  '/uploads/curriculum/cv-demo-3.pdf'),
(4, 'Marta',  'Sanchez Lopez',  'marta@trueques.local',  '$2y$10$jffo5kO6QP4AdaemjQwnv.VoHCC4aaraUQbg4R9QEEUMOO5H9VMS2', '600333333', 'PROFESIONAL', 28, '/uploads/avatars/images (5).jpg',  '/uploads/curriculum/cv-demo-4.pdf'),
(5, 'Elena',  'Romero Vidal',   'elena@trueques.local',  '$2y$10$jffo5kO6QP4AdaemjQwnv.VoHCC4aaraUQbg4R9QEEUMOO5H9VMS2', '600444444', 'PROFESIONAL', 73, '/uploads/avatars/images (1).jpg', '/uploads/curriculum/cv-demo-1.pdf'),
(6, 'Javier', 'Torres Gil',     'javier@trueques.local', '$2y$10$jffo5kO6QP4AdaemjQwnv.VoHCC4aaraUQbg4R9QEEUMOO5H9VMS2', '600555555', 'PROFESIONAL', 60, '/uploads/avatars/images (4).jpg', '/uploads/curriculum/cv-demo-2.pdf'),
(7, 'Lucia',  'Fernandez Leon', 'lucia@trueques.local',  '$2y$10$jffo5kO6QP4AdaemjQwnv.VoHCC4aaraUQbg4R9QEEUMOO5H9VMS2', '600666666', 'PROFESIONAL', 61, '/uploads/avatars/images (2).jpg', '/uploads/curriculum/cv-demo-3.pdf'),
(8, 'Pablo',  'Nunez Bravo',    'pablo@trueques.local',  '$2y$10$jffo5kO6QP4AdaemjQwnv.VoHCC4aaraUQbg4R9QEEUMOO5H9VMS2', '600777777', 'PROFESIONAL', 59, '/uploads/avatars/images (5).jpg', '/uploads/curriculum/cv-demo-4.pdf');

INSERT INTO categorias (id_categoria, nombre, descripcion) VALUES
(1, 'Hogar y reparaciones',     'Fontaneria, electricidad y pequenas obras.'),
(2, 'Informatica y tecnologia', 'Soporte tecnico y desarrollo web.'),
(3, 'Educacion y formacion',    'Clases particulares y apoyo academico.'),
(4, 'Belleza y cuidado personal','Servicios de cuidado, imagen y bienestar.'),
(5, 'Idiomas y traduccion',     'Conversacion, apoyo linguistico y traducciones.');

INSERT INTO servicios (id_servicio, id_categoria, nombre, descripcion, puntos_hora_referencia) VALUES
(1, 1, 'Reparacion de tuberias',    'Arreglo de fugas, grifos y desatascos.', 12),
(2, 1, 'Instalacion electrica',     'Enchufes, luminarias y cuadros.',        14),
(3, 2, 'Reparacion de ordenadores', 'Diagnostico, limpieza e instalacion.',   12),
(4, 2, 'Creacion de paginas web',   'Diseno y desarrollo de sitios sencillos.', 16),
(5, 3, 'Clases de matematicas',     'Refuerzo escolar y preparacion de ejercicios.', 10),
(6, 3, 'Apoyo con ingles',          'Conversacion y repaso de gramatica.', 10),
(7, 4, 'Corte de pelo',             'Corte basico y asesoramiento de estilo.', 9),
(8, 4, 'Masaje relajante',          'Sesion de relajacion de una hora.', 11),
(9, 5, 'Traduccion de textos',      'Traduccion basica entre espanol e ingles.', 13);

INSERT INTO ofertas_profesionales (id_oferta, id_profesional, id_servicio, puntos_hora, descripcion_personal, activo) VALUES
(1, 2, 1, 12, 'Fontanera con 10 anos de experiencia.', TRUE),
(2, 3, 2, 15, 'Electricista autorizado.',              TRUE),
(3, 4, 3, 12, 'Tecnica informatica: Windows y Linux.', TRUE),
(4, 5, 4, 16, 'Diseno paginas web responsivas y accesibles.', TRUE),
(5, 6, 5, 10, 'Profesor de matematicas para secundaria.', TRUE),
(6, 7, 6, 11, 'Profesora de ingles con experiencia conversacional.', TRUE),
(7, 8, 7, 9,  'Peluquera a domicilio con horario flexible.', TRUE),
(8, 5, 9, 13, 'Traduzco documentos de extension media.', TRUE),
(9, 6, 8, 11, 'Masajes relajantes en un entorno seguro y comodo.', FALSE),
(10, 8, 2, 14, 'Pequenas instalaciones electricas y mantenimiento.', TRUE);

-- SOLICITUDES: puntos_totales = puntos_hora de la oferta x horas_estimadas.
-- Cubren todos los estados, con varias solicitudes COMPLETADAS y ejemplos de CANCELADO/RECHAZADO.
INSERT INTO solicitudes_trueque
(id_solicitud, id_cliente, id_oferta, horas_estimadas, puntos_totales, fecha_propuesta, estado, confirmado_cliente, confirmado_prestador, fecha_completado) VALUES
(1, 4, 1, 2.00, 24, '2026-10-20 10:00:00', 'COMPLETADO',   TRUE,  TRUE,  '2026-10-20 12:00:00'),
(2, 2, 3, 1.00, 12, '2026-10-25 16:00:00', 'COMPLETADO',   TRUE,  TRUE,  '2026-10-25 17:00:00'),
(3, 3, 3, 2.00, 24, '2026-11-20 09:00:00', 'SOLICITADO',   FALSE, FALSE, NULL),
(4, 4, 2, 1.00, 15, '2026-11-18 12:00:00', 'ACEPTADO',     FALSE, FALSE, NULL),
(5, 2, 2, 1.00, 15, '2026-11-12 17:00:00', 'EN_EJECUCION', TRUE,  FALSE, NULL),
(6, 2, 2, 1.00, 15, '2026-10-30 14:00:00', 'COMPLETADO',   TRUE,  TRUE,  '2026-10-30 15:00:00'),
(7, 2, 4, 2.00, 32, '2026-11-21 10:30:00', 'COMPLETADO',   TRUE,  TRUE,  '2026-11-21 12:30:00'),
(8, 4, 5, 1.00, 10, '2026-11-22 18:00:00', 'COMPLETADO',   TRUE,  TRUE,  '2026-11-22 19:00:00'),
(9, 3, 6, 1.00, 11, '2026-11-23 16:00:00', 'COMPLETADO',   TRUE,  TRUE,  '2026-11-23 17:00:00'),
(10, 5, 7, 1.00, 9, '2026-11-24 09:00:00', 'COMPLETADO',   TRUE,  TRUE,  '2026-11-24 10:00:00'),
(11, 7, 8, 1.50, 20, '2026-11-24 11:15:00', 'CANCELADO',    FALSE, FALSE, NULL),
(12, 8, 4, 2.00, 32, '2026-11-23 13:00:00', 'RECHAZADO',    FALSE, FALSE, NULL),
(13, 6, 10, 2.00, 28, '2026-11-24 08:30:00', 'ACEPTADO',     FALSE, FALSE, NULL),
(14, 3, 3, 1.50, 18, '2026-11-24 12:00:00', 'EN_EJECUCION', TRUE,  FALSE, NULL),
(15, 8, 8, 1.00, 13, '2026-11-24 12:30:00', 'SOLICITADO',   FALSE, FALSE, NULL);

-- Valoraciones de ejemplo para cada profesional con servicios completados.
INSERT INTO valoraciones (id_valoracion, id_solicitud, puntuacion, comentario) VALUES
(1, 1, 5, 'Muy rapida y profesional, soluciono la fuga en una hora.'),
(2, 2, 4, 'Servicio muy bien realizado, puntual y cuidadosa.'),
(3, 6, 5, 'Trabajo excelente y explico cada paso.'),
(4, 7, 5, 'La web quedo moderna, clara y muy facil de usar.'),
(5, 8, 4, 'Las explicaciones fueron claras y sali con ejercicios utiles.'),
(6, 9, 5, 'Me ayudo a ganar confianza al hablar en ingles.'),
(7, 10, 4, 'Corte muy cuidado y buena recomendacion de estilo.');



-- Rol	Email	Contrasena
-- Administrador	admin@trueques.local	Admin1234!
-- Profesional (Laura)	laura@trueques.local	Trueque1234!
-- Profesional (Carlos)	carlos@trueques.local	Trueque1234!
-- Profesional (Marta)	marta@trueques.local	Trueque1234!
-- Profesional (Elena)	elena@trueques.local	Trueque1234!
-- Profesional (Javier)	javier@trueques.local	Trueque1234!
-- Profesional (Lucia)	lucia@trueques.local	Trueque1234!
-- Profesional (Pablo)	pablo@trueques.local	Trueque1234!
-- Aquí se añaden datos de prueba después de crear las tablas.