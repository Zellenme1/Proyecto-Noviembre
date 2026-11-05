-- Datos de prueba. TODAS las contraseñas de prueba son: password
SET NAMES utf8mb4;

INSERT INTO categorias (nombre, descripcion) VALUES
 ('Hogar', 'Reparaciones y mantenimiento del hogar'),
 ('Informática', 'Soporte técnico y desarrollo'),
 ('Educación', 'Clases particulares y formación'),
 ('Electricidad', 'Instalaciones y averías eléctricas');

INSERT INTO usuarios (nombre, apellidos, email, password, telefono, rol, puntos_saldo) VALUES
 ('Admin', 'Plataforma', 'admin@bancohoras.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '600000000', 'ADMIN', 0),
 ('Laura', 'Gómez Ruiz', 'laura@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '600111111', 'PROFESIONAL', 50),
 ('Carlos', 'Pérez Soto', 'carlos@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '600222222', 'PROFESIONAL', 50),
 ('Marta', 'López Díaz', 'marta@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '600333333', 'PROFESIONAL', 50);

INSERT INTO servicios (id_categoria, nombre, descripcion, puntos_hora_referencia) VALUES
 (1, 'Reparación de tuberías', 'Fugas, cambio de grifería y desatascos', 15),
 (2, 'Reparación de ordenadores', 'Diagnóstico y reparación de equipos', 12),
 (3, 'Clases de matemáticas', 'ESO y Bachillerato', 10),
 (4, 'Instalación eléctrica', 'Puntos de luz, enchufes y cuadros', 18);

INSERT INTO ofertas_profesionales (id_profesional, id_servicio, puntos_hora, descripcion_personal, activo) VALUES
 (2, 1, 15, '10 años de experiencia como fontanera.', TRUE),
 (3, 2, 12, 'Técnico de sistemas, reparación a domicilio.', TRUE),
 (3, 3, 10, 'Profesor de apoyo en matemáticas.', TRUE),
 (4, 4, 20, 'Electricista certificada.', TRUE);

INSERT INTO solicitudes_trueque (id_cliente, id_oferta, horas_estimadas, puntos_totales, fecha_propuesta, estado, confirmado_cliente, confirmado_prestador) VALUES
 (4, 1, 2.00, 30, '2026-01-10 10:00:00', 'COMPLETADO', TRUE, TRUE),
 (2, 2, 1.50, 18, '2026-11-05 17:00:00', 'SOLICITADO', FALSE, FALSE);

INSERT INTO valoraciones (id_solicitud, puntuacion, comentario) VALUES
 (1, 5, 'Trabajo impecable y muy puntual.');
-- Nota: el saldo de la solicitud 1 ya está reflejado manualmente:
UPDATE usuarios SET puntos_saldo = 20 WHERE id_usuario = 4;
UPDATE usuarios SET puntos_saldo = 80 WHERE id_usuario = 2;