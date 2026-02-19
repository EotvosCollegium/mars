\documentclass[11pt,a4paper]{article}

\newcommand{\resourcepath}{../public/}

\usepackage[utf8]{inputenc}
\usepackage[magyar]{babel}
\usepackage{t1enc}
\usepackage{amsmath}
\usepackage{graphicx}
\usepackage{multirow}
\usepackage{hyperref} % linkekhez
\hypersetup{
    colorlinks,
    linkcolor={red!50!black},
    citecolor={blue!50!black},
    urlcolor={blue!80!black}
}

\usepackage{caption}
\usepackage[letterspace=300]{microtype}

\usepackage{tikz}

% for the font
\usepackage{palatino}

\frenchspacing

\usepackage[left=2.5cm, right=2.5cm, top=5cm, bottom=3cm]{geometry}
\definecolor{textblue}{RGB}{34, 66, 168}
\definecolor{lineblue}{RGB}{140, 163, 216}
\date{}
\author{}
\pagenumbering{gobble}

\usepackage{parskip}
\setlength{\parindent}{0pt}

\usepackage{fancyhdr}
\setlength{\headheight}{15pt}
\setlength{\footskip}{20pt}
\pagestyle{fancy}

% \resource_path contains the path to the assets;
% it is defined in the generated tex file

\lhead{\centering  % defined this way so that chapter titles don't appear
        \begin{tikzpicture}[remember picture,overlay]
        \fill[lineblue] (-\paperwidth,1.65) rectangle (\paperwidth, 2.35);
        \fill[white] (0,2) ellipse (3.0 and 2.25);
        \node[inner sep=0pt] (picture) at (0,2){\includegraphics[width=5cm]{\resourcepath/epulet.png}};
        \node[inner sep=0pt, text=white] (from) at (-5, 2.0){\textbf{\Large \lsstyle 1895}};
        \node[inner sep=0pt, text=white] (from) at (5, 2.0){\textbf{\Large \lsstyle \the\year}};
        \node[inner sep=0pt, text=textblue] (from) at (0,0.5){\LARGE {\lsstyle EÖTVÖS COLLEGIUM}};
        \node[inner sep=0pt] (from) at (0,-0.4){\includegraphics[width=5cm]{\resourcepath/szabadon_szolgal_a_szellem.png}};
        \end{tikzpicture}
}
% let's hide the default header elements
\rhead{\fancyplain{}{}}
\renewcommand{\headrulewidth}{0pt}

\cfoot{
	{\footnotesize
	\begin{minipage}[t]{0.4\textwidth}
	  \begin{flushright}
		  \textsc{
		        \footnotesize
            \color{textblue}{
			    ELTE EÖTVÖS JÓZSEF\\
			    COLLEGIUM\\}
		    {\fontsize{7.5pt}{9.5pt}\selectfont
		    \color{black}
		    Dr. Horváth László\\
		    igazgató\\
            }
            }
		\end{flushright}
	\end{minipage}
	\hspace{1em}
	\begin{minipage}[t]{0.45\textwidth}
	  \vspace{-2.3mm}   % the dirty solution for aligning it vertically
		\begin{flushleft}
		  {\fontsize{7.5pt}{10pt}\selectfont
                    H-1118 Budapest, Ménesi út 11-13.\\
			Tel.: +36 1 460 4481 • Fax.: +36 1 209 2044\\
			E-mail:	titkarsag@eotvos.elte.hu • horvathl@eotvos.elte.hu
			elnok@eotvos.elte.hu\\
			%\vspace{3.4mm}
			Honlap: https://eotvos.elte.hu/ }
		\end{flushleft}
	\end{minipage}
	}
}

\begin{document}

\vspace*{2mm}

\begin{center}
  {\LARGE Igazolás}
\end{center}

\vspace{10mm}

Alulírott\textit{ {{ \App\Utils\LatexHelper::sanitizeLatex($approver_name) }} }hivatalosan igazolom,
hogy\textbf{ {{ \App\Utils\LatexHelper::sanitizeLatex($requester_name) }}} (Neptun-kód:\textbf{ {{ \App\Utils\LatexHelper::sanitizeLatex($requester_neptun) }}})
az alábbi tevékenységet végezte:

\begin{itemize}
\item Megnevezés:\textit{ {{ \App\Utils\LatexHelper::sanitizeLatex($service_description) }} }
@if(!is_null($date_of_service))
\item Dátum:\textit{ {{ \App\Utils\LatexHelper::sanitizeLatex($date_of_service) }} }
@endif
\end{itemize}


\vspace{30mm}

Budapest, {{ \App\Utils\LatexHelper::sanitizeLatex($current_date) }}

\hspace{100mm}
\vtop{\hsize=60mm
    \hrule\kern1ex
    \begin{center}
    \textbf{ {{ \App\Utils\LatexHelper::sanitizeLatex($approver_name) }} }\\
    \em
    {{ \App\Utils\LatexHelper::sanitizeLatex($approver_title) }}\\
    ELTE Eötvös József Collegium
    \end{center}
}

\end{document}
