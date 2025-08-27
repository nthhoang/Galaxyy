// // server.js
// const io = require("socket.io")(3000, {
//   cors: {
//     origin: "*"
//   }
// });

// const users = {}; // user_id => socket.id

// io.on("connection", (socket) => {
//   console.log("User connected:", socket.id);

//   socket.on("register", (user_id) => {
//     users[user_id] = socket.id;
//     console.log("Registered user:", user_id);
//   });

//   socket.on("send_message", (data) => {
//     const { from_user_id, to_user_id, conversation_id, message, image_path } = data;

//     // Gửi về cho chính sender
//     io.to(users[from_user_id]).emit("receive_message", data);

//     // Gửi về cho người nhận nếu đang online
//     if (users[to_user_id]) {
//       io.to(users[to_user_id]).emit("receive_message", data);
//     }
//   });

//   socket.on("disconnect", () => {
//     for (let id in users) {
//       if (users[id] === socket.id) {
//         delete users[id];
//         break;
//       }
//     }
//     console.log("User disconnected:", socket.id);
//   });

//     // Admin gửi thông báo đăng bài mới đến tất cả
//   socket.on("admin_post_article", (data) => {
//     const { message } = data;

//     console.log("New article posted, sending to all users:", message);

//     // Gửi thông báo đến tất cả user đang online
//     for (let user_id in users) {
//       io.to(users[user_id]).emit("receive_notification", {
//         type: "new_article",
//         message: message
//       });
//     }
//   });

// });

// const express = require("express");
// const app = express();
// app.use(express.json()); // Cho phép nhận body dạng JSON

// // Endpoint để PHP gọi gửi thông báo
// app.post("/notify", async (req, res) => {
//   const { message, new_id, post_id, created_at, author_id } = req.body;

//   let target_users = [];

//   if (post_id != 0) {
//     target_users = await getFollowersOfUser(author_id);
//   } else {
//     target_users = Object.keys(users);
//   }

//   for (let user_id of target_users) {
//     if (users[user_id]) {
//       io.to(users[user_id]).emit("receive_notification", {
//         type: "notification",
//         new_id,
//         post_id,
//         message,
//         created_at
//       });
//     }
//   }

//   res.json({ success: true, sent_to: target_users.length });
// });


// // Mở cổng API riêng (khác với socket)
// app.listen(4000, () => {
//   console.log("API server đang chạy tại http://localhost:4000");
// });

// const qs = require('qs');
// const axios = require('axios');
// async function getFollowersOfUser(author_id) {
//   try {
//     const response = await axios.post(
//       'http://localhost/galaxy/get_followers.php',
//       qs.stringify({ author_id: author_id }),  // <-- gửi đúng kiểu form
//       { headers: { 'Content-Type': 'application/x-www-form-urlencoded' } }
//     );
//     return response.data.followers;
//   } catch (error) {
//     console.error("Lỗi khi lấy danh sách followers:", error);
//     return [];
//   }
// }



const express = require("express");
const http = require("http");
const { Server } = require("socket.io");
const axios = require("axios");
const qs = require("qs");

const app = express();
app.use(express.json()); 

// Render/Heroku sẽ cấp cổng qua biến môi trường
const PORT = process.env.PORT || 3000;

// Tạo HTTP server cho cả Express và Socket.io
const server = http.createServer(app);

const io = new Server(server, {
  cors: {
    origin: "*"
  }
});

const users = {}; // user_id => socket.id

io.on("connection", (socket) => {
  console.log("User connected:", socket.id);

  socket.on("register", (user_id) => {
    users[user_id] = socket.id;
    console.log("Registered user:", user_id);
  });

  socket.on("send_message", (data) => {
    const { from_user_id, to_user_id } = data;

    io.to(users[from_user_id]).emit("receive_message", data);

    if (users[to_user_id]) {
      io.to(users[to_user_id]).emit("receive_message", data);
    }
  });

  socket.on("disconnect", () => {
    for (let id in users) {
      if (users[id] === socket.id) {
        delete users[id];
        break;
      }
    }
    console.log("User disconnected:", socket.id);
  });

  socket.on("admin_post_article", (data) => {
    const { message } = data;

    console.log("New article posted, sending to all users:", message);

    for (let user_id in users) {
      io.to(users[user_id]).emit("receive_notification", {
        type: "new_article",
        message: message
      });
    }
  });
});

// API endpoint cho PHP gọi
app.post("/notify", async (req, res) => {
  const { message, new_id, post_id, created_at, author_id } = req.body;

  let target_users = [];

  if (post_id != 0) {
    target_users = await getFollowersOfUser(author_id);
  } else {
    target_users = Object.keys(users);
  }

  for (let user_id of target_users) {
    if (users[user_id]) {
      io.to(users[user_id]).emit("receive_notification", {
        type: "notification",
        new_id,
        post_id,
        message,
        created_at
      });
    }
  }

  res.json({ success: true, sent_to: target_users.length });
});

async function getFollowersOfUser(author_id) {
  try {
    const response = await axios.post(
      "http://localhost/galaxy/get_followers.php", // ⚠️ Đổi localhost thành domain thật
      qs.stringify({ author_id: author_id }),
      { headers: { "Content-Type": "application/x-www-form-urlencoded" } }
    );
    return response.data.followers;
  } catch (error) {
    console.error("Lỗi khi lấy danh sách followers:", error);
    return [];
  }
}

// Chạy server
server.listen(PORT, () => {
  console.log(`Server đang chạy tại http://localhost:${PORT}`);
});
