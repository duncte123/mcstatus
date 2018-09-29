function _ (el) {
  const elements = document.querySelectorAll(el);

  if (elements.length === 1) {
    return elements[0];
  }

  return elements;
}

function fetchData (ip, port) {
  const params = {
    port: port,
    version: "1.8",
    show_img: true,
  };

  const query = Object.keys(params)
      .map((k) => encodeURIComponent(k) + "=" + encodeURIComponent(params[k]))
      .join('&');

  fetch(`/json/${ip}?${query}`)
      .catch((e) => {
        _("#app").innerHTML = `<h1>Error: <em>${e.message}</em></h1>`;
        console.error(e);
      })
      .then((r) => r.json())
      .then((data) => {

        if (!data.success) {
          _("#app").innerHTML = `<h1>Error: <em>${data.error_msg}</em></h1>`;
          return;
        }

        let output = `
            <img width="64" height="64" src="${data.img}" /> <br />
            <p>The server <strong>${data.ip}</strong> is running on <strong>${data.version}</strong> with protocol <strong>${data.protocol}</strong> and is <strong>online</strong>.</p>
            <p>There currently are <strong>${data.players}</strong> out of a maximum <strong>${data.maxplayers}</strong> online.</p>
            <p>The server has a ping of <strong>${data.ping}ms</strong> (measured from europe).</p>
            <p>The MOTD of the server is <strong>${data.motd}</strong></p>
            <br />
            `;

        if (data.playerlist) {
          output += `<p>Player list:</p>
            <br /><br />`;
          for (let player of data.playerlist) {
            console.log(player);
            output += `Name: <strong>${player.name}</strong>, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; UUID: <strong>${player.uuid}</strong> <br />`;
          }
        } else {
          output += '<p>This server is hiding their player list.</p>';
        }

        _("#app").innerHTML = output;

      });
}

document.addEventListener("DOMContentLoaded", () => {

  const vars = {};
  window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
    vars[key] = value;
  });

  const ip = vars.ip || undefined;
  const port = vars.port || 25565;

  if(typeof ip === "undefined") {
    _("#app").innerHTML = "No ip was set <br />please use \"?ip=&lt;server ip&gt;\" at the end of the url<br />";
    return;
  }

  _("title").innerHTML = ip;

  fetchData(ip, port);
});
