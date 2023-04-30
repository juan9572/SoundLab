document.addEventListener("DOMContentLoaded", function () {
  //Sacamos el contenido de cada una de las preguntas dentro del UI
  var pageContent = document.getElementById('responseform').innerHTML;
  var parser = new DOMParser();
  //Formateamos el texto de tal manera que se eliminen los tags de HTML y nos quede el texto
  var doc = parser.parseFromString(pageContent, 'text/html');
  var bodyContents = doc.body.textContent;
  // Definimos una expresión regular para extraer el texto deseado
  const regex = /Enunciado de la pregunta(.*?)(Pregunta \d|(:)|Texto de la respuesta)/gs;
  // Creamos una matriz para almacenar las coincidencias
  let matches = [];
  let match;
  // Buscamos todas las coincidencias de la expresión regular en el contenido HTML
  while ((match = regex.exec(bodyContents)) !== null) {
    matches.push(match[1].trim()); // Agregamos el texto encontrado, eliminando los espacios en blanco adicionales
  }
  // Creamos un objeto JSON para almacenar las preguntas
  const jsonString = JSON.stringify(matches);
  saveQuestions(jsonString);
});

function saveQuestions(jsonString) {
  // Le decimos a PHP que guarde el archivo json mandandoselo
  new Promise((resolve, reject) => {
    fetch('/moodle/blocks/soundlab/save_questions_from_dom.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: jsonString
    })
      .then(response => {
        if (!response.ok) {
          throw new Error('Error al guardar las preguntas');
        }
        setTimeout(() => {
          resolve();
        }, 300);
      })
      .catch(error => {
        console.error(error);
        reject(error);
      });
  });
}
