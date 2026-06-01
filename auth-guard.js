/* =====================================================================
   حارس الدخول المشترك  ·  Hejaili Digital Repository
   ---------------------------------------------------------------------
   ضع هذا الملف بجانب صفحات الموقع، واستدعِه في كل صفحة محمية بسطرين
   (انظر التعليمات في رسالة الشرح). لا تضعه في login.html.
   مصدر إعدادات فايربيس موحّد هنا فقط — لا حاجة لتكراره في كل صفحة.
   ===================================================================== */

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.1/firebase-app.js";
import { getAuth, onAuthStateChanged, signOut } from "https://www.gstatic.com/firebasejs/10.8.1/firebase-auth.js";

const firebaseConfig = {
    apiKey: "AIzaSyDF3GHcEs-By2HQBpF0905LLdV6lV2wzO4",
    authDomain: "hejaili-ec4b9.firebaseapp.com",
    projectId: "hejaili-ec4b9",
    storageBucket: "hejaili-ec4b9.firebasestorage.app",
    messagingSenderId: "656532128742",
    appId: "1:656532128742:web:b6828996ab261bea6c8c27"
};

const app  = initializeApp(firebaseConfig);
const auth = getAuth(app);

// إظهار الصفحة بعد التأكد من تسجيل الدخول (يمنع وميض المحتوى المحمي)
function reveal() { document.documentElement.classList.add('auth-ready'); }

onAuthStateChanged(auth, (user) => {
    if (!user) {
        // غير مسجّل → التحويل لصفحة الدخول (replace حتى لا يعود بزر الرجوع)
        window.location.replace("login.html");
    } else {
        // مسجّل → أظهر الصفحة
        reveal();
    }
}, (err) => {
    // خطأ في فايربيس: نُظهر الصفحة بدل تركها بيضاء، ونسجّل الخطأ
    console.error("Auth guard error:", err);
    reveal();
});

// دالة تسجيل الخروج — اربطها بأي زر عبر:  onclick="hejailiLogout()"
window.hejailiLogout = function () {
    signOut(auth).then(() => window.location.replace("login.html"));
};