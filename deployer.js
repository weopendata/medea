// Default options
var options = {
  host: '0.0.0.0',
  port: 50005,
  path: '/webhook',
  delay: 1000 * 60
}
var state = {
  busy: false,
  cmdOutput: '',
  cmdDate: '',
  ready: false
}

// Process command line arguments
process.argv.forEach(v => {
  if (v > 1000 && v < 65536) {
    options.port = v
  }
})

require('http').createServer(function(request, response) {
  if (!request.url.endsWith(options.path)) {
    response.writeHead(404)
    return response.end()
  }

  if (state.busy) {
    response.writeHead(429)
    return response.end(['Still working..', state.cmdDate, state.cmdOutput].join('\n\n'))
  }

  const ago = deploySeconds()
  if (ago >= 0 && ago < options.delay) {
    response.writeHead(429)
    return response.end(['Too soon', deployAgo(), state.cmdOutput].join('\n\n'))
  }

  deploy()

  response.end(['Deploying now!', deployAgo(), state.cmdOutput].join('\n\n'))
}).listen(options.port, options.host, 2, () => {
  state.ready = true
  console.log('Listening on', green('http://' + options.host + ':' + options.port + options.path))
})


function deploy() {
  state.busy = true
  const spawn = require('child_process').spawn
  const ls = spawn('git', ['pull'])

  ls.stdout.on('data', data => {
    state.busy = false
    state.cmdOutput = data
    state.cmdDate = Date.now()
  })
}

function deploySeconds() {
  if (!state.cmdDate) {
    return -1
  }
  return Math.round((Date.now() - state.cmdDate) / 1000)
}

function deployAgo() {
  var ago = deploySeconds()
  if (ago == -1) {
    return 'First deploy'
  }
  return 'Successfully deployed ' + ago + 's ago'
}

function green(text) {
  return '\u001b[1m\u001b[32m' + text + '\u001b[39m\u001b[22m'
}
