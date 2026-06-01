/* =====================================================================
   حارس الدخول المشترك  ·  Hejaili Digital Repository
   ---------------------------------------------------------------------
   ضعه بجانب صفحات الموقع، واستدعِه في كل صفحة محمية بسطرين.
   لا تضعه في login.html.
   ===================================================================== */

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.1/firebase-app.js";
import { getAuth, onAuthStateChanged, signOut, getIdToken } from "https://www.gstatic.com/firebasejs/10.8.1/firebase-auth.js";
import { getFirestore, doc, getDoc, setDoc, onSnapshot } from "https://www.gstatic.com/firebasejs/10.8.1/firebase-firestore.js";

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
const db   = getFirestore(app);

function reveal() { document.documentElement.classList.add('auth-ready'); }

onAuthStateChanged(auth, (user) => {
    if (!user) {
        window.location.replace("login.html");
    } else {
        reveal();
    }
}, (err) => {
    console.error("Auth guard error:", err);
    reveal();
});

// تسجيل الخروج — اربطه بأي زر:  onclick="hejailiLogout()"
window.hejailiLogout = function () {
    signOut(auth).then(() => window.location.replace("login.html"));
};

// رمز المستخدم الحالي (يُستخدم عند الحاجة)
window.hejailiToken = async function () {
    const u = auth.currentUser;
    return u ? await getIdToken(u) : null;
};

// أدوات Firestore المشتركة (تستخدمها صفحات الأقسام للمزامنة السحابية)
window.hejailiFS = {
    docRef: (col, id) => doc(db, col, id),
    getDoc:    (ref)        => getDoc(ref),
    setDoc:    (ref, data)  => setDoc(ref, data),
    onSnapshot:(ref, cb)    => onSnapshot(ref, cb)
};