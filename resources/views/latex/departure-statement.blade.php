\documentclass[11pt]{article}
\usepackage[utf8]{inputenc}
\usepackage[T1]{fontenc} % Output font encoding for international characters
\usepackage[magyar]{babel} %language
\usepackage{setspace} %for onehalfspacing

\onehalfspacing

%-----Margins-----%
\usepackage{geometry}
\geometry{
	paper=a4paper, % Change to letterpaper for US letter
	inner=2.54cm, % Inner margin
	outer=2.54cm, % Outer margin
	bindingoffset=0cm, % Binding offset
	top=2.54cm, % Top margin
	bottom=2.54cm, % Bottom margin
}

\newcommand{\lotofdots}{.....................................}

\date{}

\pagenumbering{gobble}

\begin{document}

\begin{center}
\Large \textsc{Eötvös József Collegium \\ Kiköltözési nyilatkozat} \\ \normalsize (Házirend, 3. sz. melléklet)
\end{center}

\vspace*{2em}

\noindent{}Név: {{ \App\Utils\LatexSanitizer::sanitizeLatex($name) }} \\
Állandó lakcím: {{ \App\Utils\LatexSanitizer::sanitizeLatex($address) }} \\
Telefonszám: {{ \App\Utils\LatexSanitizer::sanitizeLatex($phone) }} \\
E-mail: {{ \App\Utils\LatexSanitizer::sanitizeLatex($email) }} \\
Születési hely és idő: {{ \App\Utils\LatexSanitizer::sanitizeLatex($place_and_of_birth) }} \\
Anyja neve: {{ \App\Utils\LatexSanitizer::sanitizeLatex($mothers_name) }} \\
Kiköltözés dátuma: \\
\\
A szobát a leltár szerint átadtam.

\vspace{2em}
\noindent{}Budapest, {{ \App\Utils\LatexSanitizer::sanitizeLatex($date) }}

\hfill\lotofdots

\hfill Aláírás\hspace{3.5em}

\vspace{3em}

\hline

\vspace{2em}

\noindent{}A szobát ellenőriztük, állapota rendezett, kitakarított.\\
Budapest, {{ \App\Utils\LatexSanitizer::sanitizeLatex($date) }}

\vspace{3em}

\hfill\lotofdots

\hfill Aláírás\hspace{3.5em}

\hfill Gondnokság\hspace{2em}

\vspace{3em}

\hfill\lotofdots

\hfill Aláírás\hspace{3.5em}

\hfill Választmányi delegált

\end{document}
