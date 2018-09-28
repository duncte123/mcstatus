const _ = (el) => document.querySelectorAll(el);

function fetchData (ip, port, version) {
  const params = {
    ip: ip,
    port: port,
    version: version,
    show_img: true,
  };

  const query = Object.keys(params)
      .map((k) => encodeURIComponent(k) + "=" + encodeURIComponent(params[k]))
      .join('&').replace('&', '?');

  console.log(`Debug: ${query}`);

  fetch(`/json/${query}`)
      .then((r) => r.json())
      .then((data) => {

        let output = `
            <img width="64" height="64" src="${data.img}" /> <br />
            <p>The server <strong>${data.ip}</strong> is running on <strong>${data.version}</strong> and is <strong>online</strong>.</p>
            <p>There currently are <strong>${data.players}</strong> out of a maximum <strong>${data.maxplayers}</strong> online.</p>
            <p>The server has a ping of <strong>${data.ping}ms</strong> (measured from europe).</p>
            <p>The MOTD of the server is <strong>${data.motd}</strong></p>
            <br /><br />
            `;

        _("#app").innerHTML = output;

      })
      .error((e) => {
        alert(e.message);
        console.error(e);
      });

}

document.addEventListener("DOMDocumentLoaded", () => {

});