-- Estructura (Anexo I del enunciado)
SET NAMES utf8mb4;

CREATE TABLE categorias (
  id_categoria INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE usuarios (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  telefono VARCHAR(20),
  rol ENUM('ADMIN', 'PROFESIONAL') NOT NULL DEFAULT 'PROFESIONAL',
  puntos_saldo INT NOT NULL DEFAULT 50,
  fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE servicios (
  id_servicio INT AUTO_INCREMENT PRIMARY KEY,
  id_categoria INT NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT,
  puntos_hora_referencia INT NOT NULL DEFAULT 10,
  FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ofertas_profesionales (
  id_oferta INT AUTO_INCREMENT PRIMARY KEY,
  id_profesional INT NOT NULL,
  id_servicio INT NOT NULL,
  puntos_hora INT NOT NULL,
  descripcion_personal TEXT,
  activo BOOLEAN DEFAULT TRUE,
  FOREIGN KEY (id_profesional) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio) ON DELETE CASCADE,
  CONSTRAINT uk_profesional_servicio UNIQUE (id_profesional, id_servicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE solicitudes_trueque (
  id_solicitud INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NOT NULL,
  id_oferta INT NOT NULL,
  horas_estimadas DECIMAL(4,2) NOT NULL,
  puntos_totales INT NOT NULL,
  fecha_propuesta DATETIME NOT NULL,
  estado ENUM('SOLICITADO','ACEPTADO','RECHAZADO','EN_EJECUCION','COMPLETADO','CANCELADO') DEFAULT 'SOLICITADO',
  confirmado_cliente BOOLEAN DEFAULT FALSE,
  confirmado_prestador BOOLEAN DEFAULT FALSE,
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_cliente) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  FOREIGN KEY (id_oferta) REFERENCES ofertas_profesionales(id_oferta) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE valoraciones (
  id_valoracion INT AUTO_INCREMENT PRIMARY KEY,
  id_solicitud INT NOT NULL UNIQUE,
  puntuacion TINYINT NOT NULL CHECK (puntuacion BETWEEN 1 AND 5),
  comentario TEXT,
  fecha_valoracion DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_solicitud) REFERENCES solicitudes_trueque(id_solicitud) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;