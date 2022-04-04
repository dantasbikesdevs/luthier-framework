<?php

dataset("broken_signatures", function () {
  return [
    "dantas", // Curta
    "DANTASdantasdantasdantasdantas12", // Faltam símbolos
    "DANTASdantasdantasdantasdantas@@", // Faltam números
    "123456dantasdantasdantasdantas@@", // Faltam letras maiúsculas
    "dantasdantasdantasdantasDANTASDA", // Faltam símbolos e números
    "dantasdantasdantasdantasd@nt@sd@", // Faltam números e letras maiúsculas
    "dantasdantasdantasdantasdantas12", // Faltam símbolos e letras maiúsculas
    "dantasdantasdantasdantasdantasda", // Faltam símbolos, números e letras maiúsculas
  ];
});

dataset("broken_passwords", function () {
  return [
    "dantas", // Muito curta
    "dantas1A", // Faltam símbolos
    "dantasD@", // Faltam números
    "dantas1@", // Faltam letras maiúsculas
    "dantasDA", // Faltam símbolos e números
    "dantas11", // Faltam números e letras maiúsculas
    "dantasD1", // Faltam símbolos e letras maiúsculas
    "dantasda", // Faltam símbolos, números e letras maiúsculas
  ];
});

dataset("invalid_emails", function () {
  return [
    "dantas",
    "dantasEletro.com",
    "dantas@eletro",
    "dantas@eletro.c",
    "dantas@eletro.123",
  ];
});
