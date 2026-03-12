import socket, struct

s = socket.socket()
s.connect(('127.0.0.1', 25576))
s.settimeout(10)

# Auth
pk = struct.pack('<ii', 1, 3) + b'mcpanel2' + b'\x00\x00'
s.send(struct.pack('<i', len(pk)) + pk)
r = s.recv(4096)
print("Auth:", r)

# Send list command
pk2 = struct.pack('<ii', 2, 2) + b'list' + b'\x00\x00'
s.send(struct.pack('<i', len(pk2)) + pk2)
r2 = s.recv(4096)
print("Response:", r2)

# Parse response
if len(r2) > 12:
    print("Data:", r2[12:])

s.close()
